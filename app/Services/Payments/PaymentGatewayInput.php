<?php

namespace App\Services\Payments;

use App\Enums\PaymentMethod;

class PaymentGatewayInput
{
    public function __construct(
        public readonly PaymentMethod $method,
        public readonly ?int $installments = null,
        public readonly array $card = [],
        public readonly array $customer = [],
    ) {}
}
