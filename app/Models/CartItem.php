<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'cart_id',
    'product_sku_id',
    'shop_id',
    'shop_sku_promotion_id',
    'quantity',
])]
class CartItem extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function productSku(): BelongsTo
    {
        return $this->belongsTo(ProductSku::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(ShopSkuPromotion::class, 'shop_sku_promotion_id');
    }
}
