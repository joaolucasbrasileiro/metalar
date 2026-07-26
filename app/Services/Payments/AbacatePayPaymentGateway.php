<?php

namespace App\Services\Payments;

use App\Enums\PaymentMethod;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AbacatePayPaymentGateway implements PaymentGateway
{
    public function provider(): string
    {
        return 'abacatepay';
    }

    public function createPayment(
        Order $order,
        string $idempotencyKey,
        PaymentGatewayInput $input,
    ): PaymentGatewayResult {
        $response = Http::withToken((string) config('services.abacatepay.api_key'))
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'Idempotency-Key' => $idempotencyKey,
            ])
            ->post($this->url('/transparents/create'), $this->payload($order, $input));

        $json = $response->json();

        if (! $response->successful() || data_get($json, 'success') === false) {
            throw ValidationException::withMessages([
                'payment' => data_get($json, 'error')
                    ?: 'A AbacatePay recusou a criacao da cobranca.',
            ]);
        }

        $data = data_get($json, 'data', []);

        return new PaymentGatewayResult(
            providerReference: (string) data_get($data, 'id'),
            status: (string) data_get($data, 'status', 'PENDING'),
            checkoutUrl: data_get($data, 'url'),
            pixQrCode: data_get($data, 'brCodeBase64') ?: data_get($data, 'pix.brCodeBase64'),
            pixCopyPaste: data_get($data, 'brCode') ?: data_get($data, 'pix.brCode'),
            expiresAt: data_get($data, 'expiresAt')
                ? Carbon::parse(data_get($data, 'expiresAt'))
                : $order->expires_at,
            rawResponse: $this->redactSensitive($json ?? []),
        );
    }

    private function payload(Order $order, PaymentGatewayInput $input): array
    {
        $data = [
            'amount' => $this->amountInCents($order),
            'expiresIn' => $this->expiresIn($order),
            'description' => "Pedido #{$order->id}",
            'externalId' => "order-{$order->id}",
            'metadata' => [
                'order_id' => $order->id,
            ],
        ];

        if ($input->customer !== []) {
            $data['customer'] = $input->customer;
        }

        if ($input->method === PaymentMethod::CARD) {
            $data['card'] = $input->card;
            $data['installments'] = $input->installments ?? 1;
        }

        return [
            'method' => $input->method->providerValue(),
            'data' => $data,
        ];
    }

    private function amountInCents(Order $order): int
    {
        return (int) round(((float) $order->total) * 100);
    }

    private function expiresIn(Order $order): int
    {
        if (! $order->expires_at) {
            return (int) config('commerce.order_payment_ttl_minutes') * 60;
        }

        return max(60, now()->diffInSeconds($order->expires_at, false));
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.abacatepay.base_url'), '/').$path;
    }

    private function redactSensitive(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (in_array($key, ['number', 'cvv', 'card'], true)) {
                $payload[$key] = '[redacted]';

                continue;
            }

            if (is_array($value)) {
                $payload[$key] = $this->redactSensitive($value);
            }
        }

        return $payload;
    }
}
