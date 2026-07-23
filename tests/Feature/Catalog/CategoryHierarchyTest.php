<?php

namespace Tests\Feature\Catalog;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CategoryHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_root_category(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)
            ->postJson('/api/admin/categories', [
                'name' => 'Categoria invalida',
                'parent_id' => 999,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('parent_id');

        $this->withToken($token)
            ->postJson('/api/admin/categories', [
                'name' => 'Ferramentas',
                'description' => 'Ferramentas em geral.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Ferramentas')
            ->assertJsonPath('data.slug', 'ferramentas');

        $this->getJson('/api/categories/ferramentas')
            ->assertOk()
            ->assertJsonPath('data.name', 'Ferramentas');

        $this->assertDatabaseHas('categories', [
            'name' => 'Ferramentas',
        ]);
    }

    public function test_common_user_cannot_create_categories_or_subcategories(): void
    {
        $token = Auth::guard('api')->login(User::factory()->create());

        $this->withToken($token)
            ->postJson('/api/admin/categories', ['name' => 'Ferramentas'])
            ->assertForbidden();

        $this->withToken($token)
            ->postJson('/api/admin/subcategories', [])
            ->assertForbidden();
    }

    public function test_hierarchy_accepts_only_two_subcategory_levels(): void
    {
        $token = $this->adminToken();
        $category = $this->createCategory('Ferramentas', 'ferramentas');

        $firstLevelResponse = $this->withToken($token)
            ->postJson('/api/admin/subcategories', [
                'category_id' => $category->id,
                'name' => 'Ferramentas eletricas',
            ])
            ->assertCreated()
            ->assertJsonPath('data.level', 1);

        $firstLevelId = $firstLevelResponse->json('data.id');

        $secondLevelResponse = $this->withToken($token)
            ->postJson('/api/admin/subcategories', [
                'category_id' => $category->id,
                'parent_id' => $firstLevelId,
                'name' => 'Furadeiras',
            ])
            ->assertCreated()
            ->assertJsonPath('data.level', 2);

        $this->withToken($token)
            ->postJson('/api/admin/subcategories', [
                'category_id' => $category->id,
                'parent_id' => $secondLevelResponse->json('data.id'),
                'name' => 'Furadeiras de impacto',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('parent_id');
    }

    public function test_parent_and_child_must_belong_to_the_same_root_category(): void
    {
        $token = $this->adminToken();
        $tools = $this->createCategory('Ferramentas', 'ferramentas');
        $hydraulic = $this->createCategory('Hidraulica', 'hidraulica');
        $electricalTools = $this->createSubcategory(
            $tools,
            'Ferramentas eletricas',
            'ferramentas-eletricas',
        );

        $this->withToken($token)
            ->postJson('/api/admin/subcategories', [
                'category_id' => $hydraulic->id,
                'parent_id' => $electricalTools->id,
                'name' => 'Furadeiras',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('parent_id');
    }

    public function test_product_can_belong_to_multiple_subcategories(): void
    {
        $token = $this->adminToken();
        $tools = $this->createCategory('Ferramentas', 'ferramentas');
        $electrical = $this->createSubcategory(
            $tools,
            'Ferramentas eletricas',
            'ferramentas-eletricas',
        );
        $manual = $this->createSubcategory(
            $tools,
            'Ferramentas manuais',
            'ferramentas-manuais',
        );

        $response = $this->withToken($token)
            ->postJson('/api/admin/products', [
                'name' => 'Kit de ferramentas',
                'subcategory_ids' => [$electrical->id, $manual->id],
            ])
            ->assertCreated();

        $productId = $response->json('data.id');

        $this->assertDatabaseHas('product_subcategory', [
            'product_id' => $productId,
            'subcategory_id' => $electrical->id,
        ]);
        $this->assertDatabaseHas('product_subcategory', [
            'product_id' => $productId,
            'subcategory_id' => $manual->id,
        ]);
    }

    public function test_product_filters_include_the_selected_subcategory_children(): void
    {
        $tools = $this->createCategory('Ferramentas', 'ferramentas');
        $hydraulic = $this->createCategory('Hidraulica', 'hidraulica');

        $electrical = $this->createSubcategory(
            $tools,
            'Ferramentas eletricas',
            'ferramentas-eletricas',
        );
        $drills = $this->createSubcategory(
            $tools,
            'Furadeiras',
            'furadeiras',
            $electrical,
        );
        $pipes = $this->createSubcategory(
            $hydraulic,
            'Tubos',
            'tubos',
        );

        $genericTool = $this->createProduct('Kit eletrico', 'kit-eletrico', $electrical);
        $drill = $this->createProduct('Furadeira', 'furadeira', $drills);
        $pipe = $this->createProduct('Tubo PVC', 'tubo-pvc', $pipes);

        $categoryResponse = $this->getJson('/api/products?category=ferramentas')
            ->assertOk();

        $this->assertEqualsCanonicalizing(
            [$genericTool->id, $drill->id],
            collect($categoryResponse->json('data'))->pluck('id')->all(),
        );

        $parentResponse = $this->getJson('/api/products?subcategory=ferramentas-eletricas')
            ->assertOk();

        $this->assertEqualsCanonicalizing(
            [$genericTool->id, $drill->id],
            collect($parentResponse->json('data'))->pluck('id')->all(),
        );

        $childResponse = $this->getJson('/api/products?subcategory=furadeiras')
            ->assertOk();

        $this->assertSame(
            [$drill->id],
            collect($childResponse->json('data'))->pluck('id')->all(),
        );
    }

    public function test_categories_and_subcategories_in_use_cannot_be_deleted(): void
    {
        $token = $this->adminToken();
        $category = $this->createCategory('Ferramentas', 'ferramentas');
        $parent = $this->createSubcategory(
            $category,
            'Ferramentas eletricas',
            'ferramentas-eletricas',
        );
        $child = $this->createSubcategory(
            $category,
            'Furadeiras',
            'furadeiras',
            $parent,
        );
        $this->createProduct('Furadeira', 'furadeira', $child);

        $this->withToken($token)
            ->deleteJson('/api/admin/categories/ferramentas')
            ->assertStatus(409);

        $this->withToken($token)
            ->deleteJson('/api/admin/subcategories/ferramentas-eletricas')
            ->assertStatus(409);

        $this->withToken($token)
            ->deleteJson('/api/admin/subcategories/furadeiras')
            ->assertStatus(409);
    }

    private function adminToken(): string
    {
        return Auth::guard('api')->login(User::factory()->create([
            'role' => UserRole::ADMIN,
        ]));
    }

    private function createCategory(string $name, string $slug): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => $slug,
        ]);
    }

    private function createSubcategory(
        Category $category,
        string $name,
        string $slug,
        ?Subcategory $parent = null,
    ): Subcategory {
        return Subcategory::create([
            'category_id' => $category->id,
            'parent_id' => $parent?->id,
            'name' => $name,
            'slug' => $slug,
        ]);
    }

    private function createProduct(
        string $name,
        string $slug,
        Subcategory $subcategory,
    ): Product {
        $product = Product::create([
            'name' => $name,
            'slug' => $slug,
        ]);

        $product->subcategories()->attach($subcategory);

        return $product;
    }
}
