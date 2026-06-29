<?php

namespace Tests\Feature\Catalog;

use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandProductCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_common_user_cannot_create_a_brand(): void
    {
        $token = $this->tokenFor(User::factory()->create());

        $this->withToken($token)
            ->post('/api/admin/brands', [
                'name' => 'Bosch',
                'logo' => UploadedFile::fake()->image('bosch.png'),
            ])
            ->assertForbidden();

        $this->assertDatabaseEmpty('brands');
    }

    public function test_admin_can_create_and_update_a_brand_logo_using_multipart(): void
    {
        $token = $this->adminToken();

        $createResponse = $this->withToken($token)
            ->post('/api/admin/brands', [
                'name' => 'Bosch',
                'description' => 'Ferramentas profissionais.',
                'logo' => UploadedFile::fake()->image('bosch.png'),
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Bosch')
            ->assertJsonPath('data.slug', 'bosch');

        $brand = Brand::query()->firstOrFail();
        $oldLogoPath = $brand->logo_path;

        Storage::disk('public')->assertExists($oldLogoPath);

        $this->withToken($token)
            ->post('/api/admin/brands/bosch', [
                '_method' => 'PATCH',
                'name' => 'Bosch Professional',
                'logo' => UploadedFile::fake()->image('bosch-professional.png'),
            ])
            ->assertOk()
            ->assertJsonPath('data.slug', 'bosch-professional');

        $brand->refresh();

        Storage::disk('public')->assertMissing($oldLogoPath);
        Storage::disk('public')->assertExists($brand->logo_path);
        $this->assertNotSame($oldLogoPath, $brand->logo_path);
        $this->assertNotNull($createResponse->json('data.logo_url'));
    }

    public function test_admin_can_create_update_and_delete_a_product(): void
    {
        $token = $this->adminToken();
        $brand = $this->createBrand();

        $createResponse = $this->withToken($token)
            ->postJson('/api/admin/products', [
                'brand_id' => $brand->id,
                'name' => 'Furadeira de Impacto',
                'description' => 'Furadeira profissional.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'furadeira-de-impacto')
            ->assertJsonPath('data.brand.id', $brand->id);

        $slug = $createResponse->json('data.slug');

        $this->getJson("/api/products/{$slug}")
            ->assertOk()
            ->assertJsonPath('data.brand.name', 'Bosch');

        $this->withToken($token)
            ->patchJson("/api/admin/products/{$slug}", [
                'name' => 'Furadeira de Impacto 800W',
            ])
            ->assertOk()
            ->assertJsonPath('data.slug', 'furadeira-de-impacto-800w');

        $this->withToken($token)
            ->deleteJson('/api/admin/products/furadeira-de-impacto-800w')
            ->assertNoContent();

        $this->assertDatabaseEmpty('products');
    }

    public function test_admin_can_create_a_product_without_a_brand(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)
            ->postJson('/api/admin/products', [
                'name' => 'Areia Lavada',
                'description' => 'Produto vendido sem marca.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Areia Lavada')
            ->assertJsonPath('data.brand', null);

        $this->assertDatabaseHas('products', [
            'name' => 'Areia Lavada',
            'brand_id' => null,
        ]);
    }

    public function test_brand_with_products_cannot_be_deleted(): void
    {
        $token = $this->adminToken();
        $brand = $this->createBrand();

        Product::create([
            'brand_id' => $brand->id,
            'name' => 'Furadeira',
            'slug' => 'furadeira',
        ]);

        $this->withToken($token)
            ->deleteJson('/api/admin/brands/bosch')
            ->assertStatus(409)
            ->assertJsonPath('message', 'Nao e possivel excluir uma marca que possui produtos.');

        $this->assertDatabaseHas('brands', ['id' => $brand->id]);
    }

    private function adminToken(): string
    {
        return $this->tokenFor(User::factory()->create([
            'role' => UserRole::ADMIN,
        ]));
    }

    private function tokenFor(User $user): string
    {
        return Auth::guard('api')->login($user);
    }

    private function createBrand(): Brand
    {
        return Brand::create([
            'name' => 'Bosch',
            'slug' => 'bosch',
            'logo_path' => 'brands/logos/bosch.png',
        ]);
    }
}
