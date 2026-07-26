<?php

namespace Tests\Feature\Commerce;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\User;
use App\Services\Payments\AbacatePayPaymentGateway;
use App\Services\Payments\PaymentGatewayInput;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AbacatePayPaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_transparent_pix_payment_payload(): void
    {
        config([
            'services.abacatepay.api_key' => 'test-token',
            'services.abacatepay.base_url' => 'https://api.abacatepay.com/v2',
        ]);
        Http::fake([
            'api.abacatepay.com/v2/transparents/create' => Http::response([
                'success' => true,
                'error' => null,
                'data' => [
                    'id' => 'pix_char_123',
                    'status' => 'PENDING',
                    'brCode' => '000201',
                    'brCodeBase64' => 'data:image/png;base64,abc',
                    'expiresAt' => now()->addMinutes(15)->toISOString(),
                ],
            ]),
        ]);

        $order = $this->createOrder(100);

        $result = app(AbacatePayPaymentGateway::class)->createPayment(
            $order,
            'idem-123',
            new PaymentGatewayInput(PaymentMethod::PIX),
        );

        $this->assertSame('pix_char_123', $result->providerReference);
        $this->assertSame('000201', $result->pixCopyPaste);

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer test-token')
            && $request['method'] === 'PIX'
            && $request['data']['amount'] === 10000
            && $request['data']['externalId'] === "order-{$order->id}");
    }

    public function test_it_creates_experimental_transparent_card_payment_payload(): void
    {
        config([
            'services.abacatepay.api_key' => 'test-token',
            'services.abacatepay.base_url' => 'https://api.abacatepay.com/v2',
        ]);
        Http::fake([
            'api.abacatepay.com/v2/transparents/create' => Http::response([
                'success' => true,
                'error' => null,
                'data' => [
                    'id' => 'card_123',
                    'status' => 'APPROVED',
                    'expiresAt' => now()->addMinutes(15)->toISOString(),
                ],
            ]),
        ]);

        $order = $this->createOrder(150);

        $result = app(AbacatePayPaymentGateway::class)->createPayment(
            $order,
            'idem-456',
            new PaymentGatewayInput(
                method: PaymentMethod::CARD,
                installments: 3,
                card: [
                    'holderName' => 'Cliente Teste',
                    'number' => '4111111111111111',
                    'expirationMonth' => 12,
                    'expirationYear' => 2030,
                    'cvv' => '123',
                ],
            ),
        );

        $this->assertSame('card_123', $result->providerReference);
        $this->assertSame('APPROVED', $result->status);

        Http::assertSent(fn ($request) => $request['method'] === 'CARD'
            && $request['data']['amount'] === 15000
            && $request['data']['installments'] === 3
            && $request['data']['card']['number'] === '4111111111111111'
            && $request['data']['card']['cvv'] === '123');
    }

    private function createOrder(float $total): Order
    {
        return User::factory()
            ->create()
            ->orders()
            ->create([
                'status' => 'pending_payment',
                'subtotal' => $total,
                'total' => $total,
                'expires_at' => now()->addMinutes(15),
            ]);
    }
}
