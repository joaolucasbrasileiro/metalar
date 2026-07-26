<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case PIX = 'pix';
    case CARD = 'card';

    public function providerValue(): string
    {
        return match ($this) {
            self::PIX => 'PIX',
            self::CARD => 'CARD',
        };
    }
}
