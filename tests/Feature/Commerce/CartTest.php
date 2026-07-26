<?php

namespace Tests\Feature\Commerce;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Shop;
use App\Models\ShopSkuPrice;
use App\Models\ShopSkuPromotion;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_update_remove_and_clear_cart_items(): void
    {
        $user = User::factory()->create(['role' => UserRole::COMMON]);
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $token = Auth::guard('api')->login($user);

        $this->createOffer($shop, $sku, 10, 50);

        $cart = $this->withToken($token)
            ->postJson('/api/cart/items', [
                'product_sku_id' => $sku->id,
                'shop_id' => $shop->id,
                'quantity' => 2,
            ])
            ->assertSuccessful()
            ->assertJsonPath('data.items_count', 1)
            ->assertJsonPath('data.items.0.quantity', '2.000')
            ->json('data');

        $cartItemId = $cart['items'][0]['id'];

        $this->withToken($token)
            ->postJson('/api/cart/items', [
                'product_sku_id' => $sku->id,
                'shop_id' => $shop->id,
                'quantity' => 3,
            ])
            ->assertSuccessful()
            ->assertJsonPath('data.items_count', 1)
            ->assertJsonPath('data.items.0.quantity', '5.000');

        $this->withToken($token)
            ->patchJson("/api/cart/items/{$cartItemId}", ['quantity' => 4])
            ->assertOk()
            ->assertJsonPath('data.items.0.quantity', '4.000');

        $this->withToken($token)
            ->deleteJson("/api/cart/items/{$cartItemId}")
            ->assertOk()
            ->assertJsonPath('data.items_count', 0);

        $this->withToken($token)
            ->postJson('/api/cart/items', [
                'product_sku_id' => $sku->id,
                'shop_id' => $shop->id,
                'quantity' => 1,
            ])
            ->assertSuccessful();

        $this->withToken($token)
            ->deleteJson('/api/cart')
            ->assertOk()
            ->assertJsonPath('data.items_count', 0);
    }

    public function test_cart_item_requires_price_for_sku_in_shop(): void
    {
        $user = User::factory()->create(['role' => UserRole::COMMON]);
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();

        $this->withToken(Auth::guard('api')->login($user))
            ->postJson('/api/cart/items', [
                'product_sku_id' => $sku->id,
                'shop_id' => $shop->id,
                'quantity' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('product_sku_id');
    }

    public function test_cart_rejects_promotion_from_another_shop_sku_price(): void
    {
        $user = User::factory()->create(['role' => UserRole::COMMON]);
        $matrix = $this->createShop('matriz');
        $branch = $this->createShop('filial');
        $sku = $this->createSku();
        $matrixPrice = $this->createOffer($matrix, $sku, 10, 50);
        $this->createOffer($branch, $sku, 10, 50);
        $promotion = $this->createPromotion($matrixPrice, 40, 5);

        $this->withToken(Auth::guard('api')->login($user))
            ->postJson('/api/cart/items', [
                'product_sku_id' => $sku->id,
                'shop_id' => $branch->id,
                'promotion_id' => $promotion->id,
                'quantity' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('promotion_id');
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
    ): ShopSkuPrice {
        Stock::create([
            'warehouse_id' => $shop->warehouse->id,
            'product_sku_id' => $sku->id,
            'quantity_on_hand' => $quantity,
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
