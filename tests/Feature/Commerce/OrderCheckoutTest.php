<?php

namespace Tests\Feature\Commerce;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Shop;
use App\Models\ShopSkuPrice;
use App\Models\ShopSkuPromotion;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class OrderCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_order_from_cart_that_reserves_stock_without_starting_payment(): void
    {
        $user = User::factory()->create(['role' => UserRole::COMMON]);
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $this->createOffer($shop, $sku, 10, 50);
        $token = Auth::guard('api')->login($user);

        $this->addCartItem($token, $shop, $sku, 2);

        $response = $this->withToken($token)
            ->postJson('/api/orders')
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending_payment')
            ->assertJsonPath('data.subtotal', '100.00')
            ->assertJsonPath('data.total', '100.00')
            ->assertJsonPath('data.items.0.quantity', '2.000')
            ->assertJsonPath('data.payment_attempts', []);

        $orderId = $response->json('data.id');

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'status' => 'converted',
            'converted_order_id' => $orderId,
        ]);
        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $shop->warehouse->id,
            'product_sku_id' => $sku->id,
            'quantity_on_hand' => '10.000',
            'quantity_reserved' => '2.000',
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $orderId,
            'product_sku_id' => $sku->id,
            'shop_id' => $shop->id,
            'unit_price' => '50.00',
            'total' => '100.00',
        ]);
        $this->assertDatabaseCount('payment_attempts', 0);
    }

    public function test_payment_attempt_route_reuses_active_attempt_for_same_order(): void
    {
        $user = User::factory()->create(['role' => UserRole::COMMON]);
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $this->createOffer($shop, $sku, 10, 50);
        $token = Auth::guard('api')->login($user);

        $this->addCartItem($token, $shop, $sku, 1);

        $orderId = $this->withToken($token)
            ->postJson('/api/orders')
            ->assertCreated()
            ->json('data.id');

        $firstAttemptId = $this->withToken($token)
            ->postJson("/api/orders/{$orderId}/payment-attempts", [
                'method' => 'pix',
            ])
            ->assertCreated()
            ->assertJsonPath('data.provider', 'fake')
            ->assertJsonPath('data.method', 'pix')
            ->assertJsonPath('data.status', 'pending')
            ->json('data.id');

        $this->withToken($token)
            ->postJson("/api/orders/{$orderId}/payment-attempts", [
                'method' => 'pix',
            ])
            ->assertOk()
            ->assertJsonPath('data.id', $firstAttemptId);

        $this->assertDatabaseCount('payment_attempts', 1);
    }

    public function test_fake_payment_approval_commits_reserved_stock_as_sale(): void
    {
        $user = User::factory()->create(['role' => UserRole::COMMON]);
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $price = $this->createOffer($shop, $sku, 10, 50);
        $promotion = $this->createPromotion($price, 40, 5);
        $token = Auth::guard('api')->login($user);

        $this->addCartItem($token, $shop, $sku, 2, $promotion);

        $order = $this->withToken($token)
            ->postJson('/api/orders')
            ->assertCreated()
            ->assertJsonPath('data.items.0.unit_price', '40.00')
            ->json('data');

        $paymentAttemptId = $this->withToken($token)
            ->postJson("/api/orders/{$order['id']}/payment-attempts", [
                'method' => 'pix',
            ])
            ->assertCreated()
            ->json('data.id');

        $this->withToken($token)
            ->postJson("/api/orders/{$order['id']}/payment-attempts/{$paymentAttemptId}/fake-approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('orders', [
            'id' => $order['id'],
            'status' => OrderStatus::PAID->value,
        ]);
        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $shop->warehouse->id,
            'product_sku_id' => $sku->id,
            'quantity_on_hand' => '8.000',
            'quantity_reserved' => '0.000',
        ]);
        $this->assertDatabaseHas('shop_sku_promotions', [
            'id' => $promotion->id,
            'quantity_reserved' => '0.000',
            'quantity_sold' => '2.000',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'type' => 'sale',
            'quantity' => '-2.000',
            'quantity_before' => '10.000',
            'quantity_after' => '8.000',
        ]);
    }

    public function test_cancelled_pending_order_releases_reserved_stock(): void
    {
        $user = User::factory()->create(['role' => UserRole::COMMON]);
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $this->createOffer($shop, $sku, 10, 50);
        $token = Auth::guard('api')->login($user);

        $this->addCartItem($token, $shop, $sku, 3);

        $orderId = $this->withToken($token)
            ->postJson('/api/orders')
            ->assertCreated()
            ->json('data.id');

        $this->withToken($token)
            ->postJson("/api/orders/{$orderId}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $shop->warehouse->id,
            'product_sku_id' => $sku->id,
            'quantity_on_hand' => '10.000',
            'quantity_reserved' => '0.000',
        ]);
    }

    public function test_card_payment_attempt_accepts_card_payload_without_storing_raw_card_data(): void
    {
        $user = User::factory()->create(['role' => UserRole::COMMON]);
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $this->createOffer($shop, $sku, 10, 50);
        $token = Auth::guard('api')->login($user);

        $this->addCartItem($token, $shop, $sku, 1);

        $orderId = $this->withToken($token)
            ->postJson('/api/orders')
            ->assertCreated()
            ->json('data.id');

        $this->withToken($token)
            ->postJson("/api/orders/{$orderId}/payment-attempts", [
                'method' => 'card',
                'installments' => 2,
                'card' => [
                    'holder_name' => 'Cliente Teste',
                    'number' => '4111111111111111',
                    'expiration_month' => 12,
                    'expiration_year' => 2030,
                    'cvv' => '123',
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.method', 'card')
            ->assertJsonPath('data.provider', 'fake');

        $attempt = Order::findOrFail($orderId)->paymentAttempts()->firstOrFail();

        $this->assertSame('card', $attempt->method->value);
        $this->assertStringNotContainsString('4111111111111111', json_encode($attempt->raw_response));
        $this->assertStringNotContainsString('123', json_encode($attempt->raw_response));
    }

    public function test_expiration_command_marks_pending_order_as_expired_and_releases_stock(): void
    {
        $user = User::factory()->create(['role' => UserRole::COMMON]);
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $this->createOffer($shop, $sku, 10, 50);
        $token = Auth::guard('api')->login($user);

        $this->addCartItem($token, $shop, $sku, 4);

        $orderId = $this->withToken($token)
            ->postJson('/api/orders')
            ->assertCreated()
            ->json('data.id');

        Order::findOrFail($orderId)->update(['expires_at' => now()->subMinute()]);

        Artisan::call('orders:expire-pending');

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => OrderStatus::EXPIRED->value,
        ]);
        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $shop->warehouse->id,
            'product_sku_id' => $sku->id,
            'quantity_on_hand' => '10.000',
            'quantity_reserved' => '0.000',
        ]);
    }

    public function test_order_cannot_reserve_more_than_available_stock(): void
    {
        $user = User::factory()->create(['role' => UserRole::COMMON]);
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $this->createOffer($shop, $sku, 2, 50);
        $token = Auth::guard('api')->login($user);

        $this->addCartItem($token, $shop, $sku, 3);

        $this->withToken($token)
            ->postJson('/api/orders')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');

        $this->assertDatabaseMissing('orders', [
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'status' => 'active',
        ]);
    }

    public function test_order_requires_active_cart_with_items(): void
    {
        $user = User::factory()->create(['role' => UserRole::COMMON]);

        $this->withToken(Auth::guard('api')->login($user))
            ->postJson('/api/orders')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cart');

        $this->assertDatabaseMissing('orders', [
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'status' => 'active',
        ]);
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

    private function addCartItem(
        string $token,
        Shop $shop,
        ProductSku $sku,
        float $quantity,
        ?ShopSkuPromotion $promotion = null,
    ): void {
        $this->withToken($token)
            ->postJson('/api/cart/items', [
                'product_sku_id' => $sku->id,
                'shop_id' => $shop->id,
                'promotion_id' => $promotion?->id,
                'quantity' => $quantity,
            ])
            ->assertSuccessful();
    }
}
