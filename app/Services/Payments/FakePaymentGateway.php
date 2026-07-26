<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Support\Str;

class FakePaymentGateway implements PaymentGateway
{
    public function provider(): string
    {
        return 'fake';
    }

    public function createPayment(
        Order $order,
        string $idempotencyKey,
        PaymentGatewayInput $input,
    ): PaymentGatewayResult {
        $reference = 'fake_'.$input->method->value.'_'.$order->id.'_'
            .Str::of($idempotencyKey)->replace('-', '')->limit(16, '');

        return new PaymentGatewayResult(
            providerReference: $reference,
            status: 'pending',
            checkoutUrl: $input->method->value === 'card' ? null : url("/fake-checkout/{$reference}"),
            pixQrCode: null,
            pixCopyPaste: $input->method->value === 'pix' ? "fake-pix-copy-paste-{$reference}" : null,
            expiresAt: $order->expires_at,
            rawResponse: [
                'provider' => 'fake',
                'method' => $input->method->value,
                'reference' => $reference,
                'order_id' => $order->id,
                'amount' => $order->total,
            ],
        );
    }
}
