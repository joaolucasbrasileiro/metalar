<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'product_id',
    'product_sku_id',
    'shop_id',
    'shop_sku_promotion_id',
    'quantity',
    'regular_unit_price',
    'unit_price',
    'discount_total',
    'total',
    'product_name',
    'product_sku',
    'shop_name',
])]
class OrderItem extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'regular_unit_price' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
