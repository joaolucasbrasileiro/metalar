<?php

namespace Tests\Feature\Catalog;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSku;
use App\Models\Shop;
use App\Models\ShopSkuPrice;
use App\Models\ShopSkuPromotion;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfferCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_admin_can_create_a_product_sku(): void
    {
        $product = $this->createProduct('Cimento Votoran', 'cimento-votoran');

        $response = $this->withToken($this->adminToken())
            ->postJson('/api/admin/product-skus', [
                'product_id' => $product->id,
                'sku' => ' cim-vot-50 ',
                'barcode' => '789000000001',
                'unit' => 'SACO',
                'weight' => 50,
                'transfer_batch_quantity' => 5,
                'transfer_fee_per_batch' => 3,
            ])
            ->assertCreated()
            ->assertJsonPath('data.sku', 'CIM-VOT-50')
            ->assertJsonPath('data.unit', 'saco')
            ->assertJsonPath('data.transfer.minimum_fee', '7.00');

        $this->getJson('/api/product-skus/CIM-VOT-50')
            ->assertOk()
            ->assertJsonPath('data.id', $response->json('data.id'));
    }

    public function test_admin_can_upload_a_product_image(): void
    {
        $product = $this->createProduct('Furadeira', 'furadeira');

        $response = $this->withToken($this->adminToken())
            ->post('/api/admin/product-images', [
                'product_id' => $product->id,
                'image' => UploadedFile::fake()->image('furadeira.png'),
                'alt_text' => 'Furadeira vista frontal',
            ])
            ->assertCreated()
            ->assertJsonPath('data.is_primary', true);

        $productImage = ProductImage::findOrFail($response->json('data.id'));

        Storage::disk('public')->assertExists($productImage->image_path);
    }

    public function test_only_assigned_moderator_can_adjust_a_shop_stock(): void
    {
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $moderator = User::factory()->create(['role' => UserRole::MODERATOR]);
        $token = Auth::guard('api')->login($moderator);
        $url = "/api/staff/shops/{$shop->code}/product-skus/{$sku->sku}/stock-adjustments";

        $this->withToken($token)
            ->postJson($url, ['quantity' => 10, 'reason' => 'Entrada inicial'])
            ->assertForbidden();

        $moderator->shops()->attach($shop);

        $this->withToken($token)
            ->postJson($url, ['quantity' => 10, 'reason' => 'Entrada inicial'])
            ->assertOk()
            ->assertJsonPath('data.quantity_on_hand', '10.000');

        $this->assertDatabaseHas('stock_movements', [
            'user_id' => $moderator->id,
            'type' => 'adjustment',
            'reason' => 'Entrada inicial',
        ]);
    }

    public function test_admin_can_adjust_stock_for_any_shop(): void
    {
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();

        $this->withToken($this->adminToken())
            ->postJson(
                "/api/staff/shops/{$shop->code}/product-skus/{$sku->sku}/stock-adjustments",
                ['quantity' => 7, 'reason' => 'Entrada por administrador'],
            )
            ->assertOk()
            ->assertJsonPath('data.quantity_on_hand', '7.000');
    }

    public function test_moderator_can_update_price_only_for_assigned_shop(): void
    {
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $moderator = User::factory()->create(['role' => UserRole::MODERATOR]);
        $token = Auth::guard('api')->login($moderator);
        $url = "/api/staff/shops/{$shop->code}/product-skus/{$sku->sku}/price";

        $this->withToken($token)
            ->putJson($url, ['price' => 59.9])
            ->assertForbidden();

        $moderator->shops()->attach($shop);

        $this->withToken($token)
            ->putJson($url, ['price' => 59.9])
            ->assertCreated()
            ->assertJsonPath('data.price', '59.90')
            ->assertJsonPath('data.product_sku_id', $sku->id);
    }

    public function test_shop_sku_price_can_be_created_and_updated_without_duplicates(): void
    {
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $token = $this->adminToken();
        $url = "/api/staff/shops/{$shop->code}/product-skus/{$sku->sku}/price";

        $this->withToken($token)
            ->putJson($url, ['price' => 50])
            ->assertCreated()
            ->assertJsonPath('data.price', '50.00');

        $this->withToken($token)
            ->putJson($url, ['price' => 45.75])
            ->assertOk()
            ->assertJsonPath('data.price', '45.75');

        $this->assertSame(1, ShopSkuPrice::count());
        $this->assertDatabaseHas('shop_sku_prices', [
            'shop_id' => $shop->id,
            'product_sku_id' => $sku->id,
            'price' => '45.75',
        ]);
    }

    public function test_price_cannot_be_removed_while_it_has_an_active_promotion(): void
    {
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $price = $this->createOffer($shop, $sku, 10, 50);
        $this->createPromotion($price, 40, 5);

        $this->withToken($this->adminToken())
            ->deleteJson("/api/staff/shops/{$shop->code}/product-skus/{$sku->sku}/price")
            ->assertConflict()
            ->assertJsonPath('message', 'Cancele as promocoes antes de excluir o preco.');

        $this->assertDatabaseHas('shop_sku_prices', [
            'id' => $price->id,
        ]);
    }

    public function test_promotion_is_limited_by_shop_available_stock(): void
    {
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $token = $this->adminToken();

        $this->withToken($token)
            ->postJson(
                "/api/staff/shops/{$shop->code}/product-skus/{$sku->sku}/stock-adjustments",
                ['quantity' => 10, 'reason' => 'Entrada inicial'],
            )
            ->assertOk();

        $this->withToken($token)
            ->putJson(
                "/api/staff/shops/{$shop->code}/product-skus/{$sku->sku}/price",
                ['price' => 50],
            )
            ->assertSuccessful();

        $this->withToken($token)
            ->postJson(
                "/api/staff/shops/{$shop->code}/product-skus/{$sku->sku}/promotions",
                ['promotional_price' => 40, 'quantity_limit' => 11],
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quantity_limit');

        $this->withToken($token)
            ->postJson(
                "/api/staff/shops/{$shop->code}/product-skus/{$sku->sku}/promotions",
                ['promotional_price' => 40, 'quantity_limit' => 5],
            )
            ->assertCreated()
            ->assertJsonPath('data.quantity_remaining', '5.000')
            ->assertJsonPath('data.is_active', true);
    }

    public function test_reserved_stock_reduces_available_quantity_for_promotions(): void
    {
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $token = $this->adminToken();

        $this->createOffer($shop, $sku, 10, 50, reservedQuantity: 4);

        $this->withToken($token)
            ->postJson(
                "/api/staff/shops/{$shop->code}/product-skus/{$sku->sku}/promotions",
                ['promotional_price' => 40, 'quantity_limit' => 7],
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quantity_limit');

        $this->withToken($token)
            ->postJson(
                "/api/staff/shops/{$shop->code}/product-skus/{$sku->sku}/promotions",
                ['promotional_price' => 40, 'quantity_limit' => 6],
            )
            ->assertCreated()
            ->assertJsonPath('data.quantity_remaining', '6.000');
    }

    public function test_stock_adjustment_cannot_reduce_stock_below_reserved_quantity(): void
    {
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $token = $this->adminToken();

        $this->createOffer($shop, $sku, 10, 50, reservedQuantity: 4);

        $this->withToken($token)
            ->postJson(
                "/api/staff/shops/{$shop->code}/product-skus/{$sku->sku}/stock-adjustments",
                ['quantity' => -7, 'reason' => 'Ajuste de inventario'],
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');

        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $shop->warehouse->id,
            'product_sku_id' => $sku->id,
            'quantity_on_hand' => '10.000',
            'quantity_reserved' => '4.000',
        ]);
    }

    public function test_promotion_price_must_be_lower_than_regular_price(): void
    {
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $token = $this->adminToken();

        $this->createOffer($shop, $sku, 10, 50);

        $this->withToken($token)
            ->postJson(
                "/api/staff/shops/{$shop->code}/product-skus/{$sku->sku}/promotions",
                ['promotional_price' => 50, 'quantity_limit' => 1],
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('promotional_price');
    }

    public function test_shop_cannot_have_two_open_promotions_for_the_same_sku(): void
    {
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $token = $this->adminToken();

        $this->createOffer($shop, $sku, 10, 50);

        $this->withToken($token)
            ->postJson(
                "/api/staff/shops/{$shop->code}/product-skus/{$sku->sku}/promotions",
                ['promotional_price' => 40, 'quantity_limit' => 5],
            )
            ->assertCreated();

        $this->withToken($token)
            ->postJson(
                "/api/staff/shops/{$shop->code}/product-skus/{$sku->sku}/promotions",
                ['promotional_price' => 35, 'quantity_limit' => 1],
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('promotional_price');

        $this->assertSame(1, ShopSkuPromotion::count());
    }

    public function test_cancelled_promotion_allows_a_new_promotion_for_the_same_sku(): void
    {
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $token = $this->adminToken();

        $this->createOffer($shop, $sku, 10, 50);

        $promotionId = $this->withToken($token)
            ->postJson(
                "/api/staff/shops/{$shop->code}/product-skus/{$sku->sku}/promotions",
                ['promotional_price' => 40, 'quantity_limit' => 5],
            )
            ->assertCreated()
            ->json('data.id');

        $this->withToken($token)
            ->deleteJson("/api/staff/shops/{$shop->code}/promotions/{$promotionId}")
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertNotNull(ShopSkuPromotion::findOrFail($promotionId)->cancelled_at);

        $this->withToken($token)
            ->postJson(
                "/api/staff/shops/{$shop->code}/product-skus/{$sku->sku}/promotions",
                ['promotional_price' => 35, 'quantity_limit' => 1],
            )
            ->assertCreated();
    }

    public function test_catalog_returns_and_orders_by_the_best_available_offer(): void
    {
        $matrix = $this->createShop('matriz');
        $branch = $this->createShop('filial');

        $cement = $this->createProduct('Cimento', 'cimento');
        $cementSku = $this->createSku($cement, 'CIMENTO-50');
        $drill = $this->createProduct('Furadeira', 'furadeira');
        $drillSku = $this->createSku($drill, 'FURADEIRA-800');

        $this->createOffer($matrix, $cementSku, 10, 50);
        $branchCementPrice = $this->createOffer($branch, $cementSku, 5, 48);
        $this->createPromotion($branchCementPrice, 40, 2);
        $this->createOffer($matrix, $drillSku, 3, 35);

        $response = $this->getJson('/api/products')
            ->assertOk();

        $this->assertSame(
            [$drill->id, $cement->id],
            collect($response->json('data'))->pluck('id')->all(),
        );

        $cementData = collect($response->json('data'))
            ->firstWhere('id', $cement->id);

        $this->assertSame('40.00', $cementData['skus'][0]['best_offer']['effective_price']);
        $this->assertSame('filial', $cementData['skus'][0]['best_offer']['shop']['code']);
        $this->assertTrue($cementData['skus'][0]['best_offer']['is_promotion']);
        $this->assertSame('2.000', $cementData['skus'][0]['best_offer']['available_quantity']);
        $this->assertSame('15.000', $cementData['skus'][0]['total_available']);
    }

    public function test_catalog_does_not_expose_available_offer_for_sku_without_stock(): void
    {
        $shop = $this->createShop('matriz');
        $product = $this->createProduct('Argamassa', 'argamassa');
        $sku = $this->createSku($product, 'ARGAMASSA-20');

        ShopSkuPrice::create([
            'shop_id' => $shop->id,
            'product_sku_id' => $sku->id,
            'price' => 25,
        ]);

        $productData = collect($this->getJson('/api/products')->assertOk()->json('data'))
            ->firstWhere('id', $product->id);

        $this->assertSame('0.000', $productData['skus'][0]['total_available']);
        $this->assertNull($productData['skus'][0]['best_offer']);
        $this->assertSame([], $productData['skus'][0]['offers']);

        $this->getJson('/api/products?in_stock=1')
            ->assertOk()
            ->assertJsonMissing(['id' => $product->id]);
    }

    private function adminToken(): string
    {
        return Auth::guard('api')->login(User::factory()->create([
            'role' => UserRole::ADMIN,
        ]));
    }

    private function createProduct(string $name = 'Produto', string $slug = 'produto'): Product
    {
        return Product::create([
            'name' => $name,
            'slug' => $slug,
        ]);
    }

    private function createSku(
        ?Product $product = null,
        string $sku = 'SKU-001',
    ): ProductSku {
        return ProductSku::create([
            'product_id' => ($product ?? $this->createProduct())->id,
            'sku' => $sku,
            'unit' => 'un',
            'transfer_batch_quantity' => 1,
            'transfer_fee_per_batch' => 0,
        ]);
    }

    private function createShop(string $code): Shop
    {
        $number = $code === 'matriz' ? '1' : '2';

        $shop = Shop::create([
            'code' => $code,
            'name' => ucfirst($code),
            'cnpj' => str_pad($number, 14, '0', STR_PAD_LEFT),
            'phone' => '7199999999'.$number,
            'zip_code' => '46430000',
            'street' => 'Rua principal',
            'number' => $number,
            'neighborhood' => 'Centro',
            'city' => 'Guanambi',
            'state' => 'BA',
        ]);

        $shop->warehouse()->create(['name' => "Warehouse {$code}"]);

        return $shop;
    }

    private function createOffer(
        Shop $shop,
        ProductSku $sku,
        float $quantity,
        float $price,
        float $reservedQuantity = 0,
    ): ShopSkuPrice {
        Stock::create([
            'warehouse_id' => $shop->warehouse->id,
            'product_sku_id' => $sku->id,
            'quantity_on_hand' => $quantity,
            'quantity_reserved' => $reservedQuantity,
        ]);

        return ShopSkuPrice::create([
            'shop_id' => $shop->id,
            'product_sku_id' => $sku->id,
            'price' => $price,
        ]);
    }

    private function createPromotion(
        ShopSkuPrice $price,
        float $promotionalPrice,
        float $quantity,
    ): ShopSkuPromotion {
        return ShopSkuPromotion::create([
            'shop_sku_price_id' => $price->id,
            'created_by_user_id' => User::factory()->create([
                'role' => UserRole::ADMIN,
            ])->id,
            'promotional_price' => $promotionalPrice,
            'quantity_limit' => $quantity,
            'starts_at' => now()->subMinute(),
        ]);
    }
}
