<?php

namespace App\Services\Payments;

use App\Models\Order;

interface PaymentGateway
{
    public function provider(): string;

    public function createPayment(
        Order $order,
        string $idempotencyKey,
        PaymentGatewayInput $input,
    ): PaymentGatewayResult;
}
