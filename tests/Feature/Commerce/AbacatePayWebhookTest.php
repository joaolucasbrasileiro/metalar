<?php

namespace Tests\Feature\Commerce;

use App\Enums\OrderStatus;
use App\Enums\PaymentAttemptStatus;
use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Shop;
use App\Models\ShopSkuPrice;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AbacatePayWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const WEBHOOK_SECRET = 'webhook-secret';

    private const WEBHOOK_PUBLIC_KEY = 'webhook-public-key';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.abacatepay.webhook_secret' => self::WEBHOOK_SECRET,
            'services.abacatepay.webhook_public_key' => self::WEBHOOK_PUBLIC_KEY,
        ]);
    }

    public function test_it_rejects_invalid_webhook_secret(): void
    {
        $payload = $this->completedPayload('evt_invalid', 'transparent_123');

        $this->postWebhook($payload, secret: 'wrong-secret')
            ->assertUnauthorized();

        $this->assertDatabaseCount('abacate_pay_webhook_events', 0);
    }

    public function test_it_rejects_invalid_webhook_signature(): void
    {
        $payload = $this->completedPayload('evt_invalid_signature', 'transparent_123');

        $this->postWebhook($payload, signature: 'invalid-signature')
            ->assertUnauthorized();

        $this->assertDatabaseCount('abacate_pay_webhook_events', 0);
    }

    public function test_completed_webhook_approves_payment_and_commits_stock(): void
    {
        [$order, $attempt, $shop, $sku] = $this->createPendingOrderWithAbacatePayAttempt('transparent_paid_123');

        $this->postWebhook($this->completedPayload('evt_paid_123', $attempt->provider_reference))
            ->assertOk()
            ->assertJsonPath('data.provider_event_id', 'evt_paid_123')
            ->assertJsonPath('data.event', 'transparent.completed');

        $this->assertDatabaseHas('payment_attempts', [
            'id' => $attempt->id,
            'status' => PaymentAttemptStatus::APPROVED->value,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::PAID->value,
        ]);
        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $shop->warehouse->id,
            'product_sku_id' => $sku->id,
            'quantity_on_hand' => '9.000',
            'quantity_reserved' => '0.000',
        ]);
        $this->assertDatabaseHas('abacate_pay_webhook_events', [
            'provider_event_id' => 'evt_paid_123',
            'event' => 'transparent.completed',
            'provider_reference' => 'transparent_paid_123',
            'payment_attempt_id' => $attempt->id,
        ]);

        $this->assertNotNull($attempt->refresh()->raw_response['last_webhook'] ?? null);
    }

    public function test_completed_webhook_is_idempotent(): void
    {
        [$order, $attempt] = $this->createPendingOrderWithAbacatePayAttempt('transparent_idem_123');
        $payload = $this->completedPayload('evt_idem_123', $attempt->provider_reference);

        $this->postWebhook($payload)->assertOk();
        $this->postWebhook($payload)->assertOk();

        $this->assertDatabaseCount('abacate_pay_webhook_events', 1);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::PAID->value,
        ]);
        $this->assertDatabaseCount('stock_movements', 2);
        $this->assertDatabaseCount('payment_attempts', 1);
    }

    public function test_unknown_payment_reference_is_recorded_as_failed_event(): void
    {
        $this->postWebhook($this->completedPayload('evt_unknown_123', 'transparent_missing_123'))
            ->assertNotFound();

        $this->assertDatabaseHas('abacate_pay_webhook_events', [
            'provider_event_id' => 'evt_unknown_123',
            'provider_reference' => 'transparent_missing_123',
        ]);
        $this->assertNotNull(
            app('db')
                ->table('abacate_pay_webhook_events')
                ->where('provider_event_id', 'evt_unknown_123')
                ->value('failed_at'),
        );
    }

    public function test_refunded_webhook_marks_payment_attempt_as_refunded_without_changing_order(): void
    {
        [$order, $attempt] = $this->createPendingOrderWithAbacatePayAttempt('transparent_refund_123');
        $attempt->update([
            'status' => PaymentAttemptStatus::APPROVED,
            'approved_at' => now(),
        ]);
        $order->update([
            'status' => OrderStatus::PAID,
            'paid_at' => now(),
        ]);

        $this->postWebhook($this->payload('evt_refund_123', 'transparent.refunded', $attempt->provider_reference))
            ->assertOk();

        $this->assertDatabaseHas('payment_attempts', [
            'id' => $attempt->id,
            'status' => PaymentAttemptStatus::REFUNDED->value,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::PAID->value,
        ]);
    }

    private function postWebhook(
        array $payload,
        ?string $secret = null,
        ?string $signature = null,
    ) {
        $body = json_encode($payload);
        $signature ??= base64_encode(hash_hmac('sha256', $body, self::WEBHOOK_PUBLIC_KEY, true));

        return $this->call(
            'POST',
            '/api/webhooks/abacatepay?webhookSecret='.($secret ?? self::WEBHOOK_SECRET),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_WEBHOOK_SIGNATURE' => $signature,
            ],
            $body,
        );
    }

    private function completedPayload(string $eventId, string $providerReference): array
    {
        return $this->payload($eventId, 'transparent.completed', $providerReference);
    }

    private function payload(string $eventId, string $eventName, string $providerReference): array
    {
        return [
            'id' => $eventId,
            'event' => $eventName,
            'data' => [
                'transparent' => [
                    'id' => $providerReference,
                    'status' => strtoupper(str($eventName)->afterLast('.')->value()),
                ],
            ],
        ];
    }

    private function createPendingOrderWithAbacatePayAttempt(string $providerReference): array
    {
        $user = User::factory()->create(['role' => UserRole::COMMON]);
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $this->createOffer($shop, $sku, 10, 50);
        $token = Auth::guard('api')->login($user);

        $this->withToken($token)
            ->postJson('/api/cart/items', [
                'product_sku_id' => $sku->id,
                'shop_id' => $shop->id,
                'quantity' => 1,
            ])
            ->assertSuccessful();

        $orderId = $this->withToken($token)
            ->postJson('/api/orders')
            ->assertCreated()
            ->json('data.id');

        $order = Order::query()->findOrFail($orderId);
        $attempt = $order->paymentAttempts()->create([
            'provider' => 'abacatepay',
            'method' => PaymentMethod::PIX,
            'provider_reference' => $providerReference,
            'status' => PaymentAttemptStatus::PENDING,
            'amount' => $order->total,
            'expires_at' => $order->expires_at,
            'started_at' => now(),
            'idempotency_key' => 'idem-'.$providerReference,
            'raw_response' => [
                'data' => [
                    'id' => $providerReference,
                ],
            ],
        ]);

        return [$order, $attempt, $shop, $sku];
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
}
