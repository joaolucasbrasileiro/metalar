<?php

namespace App\Services\Payments;

use Illuminate\Support\Carbon;

class PaymentGatewayResult
{
    public function __construct(
        public readonly string $providerReference,
        public readonly string $status,
        public readonly ?string $checkoutUrl,
        public readonly ?string $pixQrCode,
        public readonly ?string $pixCopyPaste,
        public readonly ?Carbon $expiresAt,
        public readonly array $rawResponse = [],
    ) {}
}
