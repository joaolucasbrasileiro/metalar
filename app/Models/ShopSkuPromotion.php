<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'shop_sku_price_id',
    'created_by_user_id',
    'promotional_price',
    'quantity_limit',
    'quantity_reserved',
    'quantity_sold',
    'starts_at',
    'ends_at',
    'cancelled_at',
])]
class ShopSkuPromotion extends Model
{
    protected function casts(): array
    {
        return [
            'promotional_price' => 'decimal:2',
            'quantity_limit' => 'decimal:3',
            'quantity_reserved' => 'decimal:3',
            'quantity_sold' => 'decimal:3',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function shopSkuPrice(): BelongsTo
    {
        return $this->belongsTo(ShopSkuPrice::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function remainingQuantity(): float
    {
        return max(
            0,
            (float) $this->quantity_limit
                - (float) $this->quantity_reserved
                - (float) $this->quantity_sold,
        );
    }

    public function isActive(?Carbon $at = null): bool
    {
        $at ??= now();

        return $this->cancelled_at === null
            && $this->starts_at <= $at
            && ($this->ends_at === null || $this->ends_at >= $at)
            && $this->remainingQuantity() > 0;
    }
}
