<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'status',
    'subtotal',
    'discount_total',
    'total',
    'expires_at',
    'paid_at',
    'cancelled_at',
    'expired_at',
])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'total' => 'decimal:2',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentAttempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function isPayable(): bool
    {
        return $this->status === OrderStatus::PENDING_PAYMENT
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
