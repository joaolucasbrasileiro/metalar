<?php

namespace App\Models;

use App\Enums\PaymentAttemptStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'provider',
    'method',
    'provider_reference',
    'status',
    'amount',
    'checkout_url',
    'pix_qr_code',
    'pix_copy_paste',
    'expires_at',
    'started_at',
    'approved_at',
    'failed_at',
    'cancelled_at',
    'idempotency_key',
    'raw_response',
])]
class PaymentAttempt extends Model
{
    protected function casts(): array
    {
        return [
            'status' => PaymentAttemptStatus::class,
            'method' => PaymentMethod::class,
            'amount' => 'decimal:2',
            'expires_at' => 'datetime',
            'started_at' => 'datetime',
            'approved_at' => 'datetime',
            'failed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'raw_response' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isReusable(): bool
    {
        return $this->status === PaymentAttemptStatus::PENDING
            && ($this->expires_at === null || $this->expires_at->isFuture())
            && $this->method !== PaymentMethod::CARD
            && ($this->checkout_url !== null
                || $this->pix_qr_code !== null
                || $this->pix_copy_paste !== null);
    }
}
