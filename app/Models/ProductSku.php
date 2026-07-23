<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'product_id',
    'sku',
    'barcode',
    'unit',
    'weight',
    'length',
    'width',
    'height',
    'transfer_batch_quantity',
    'transfer_fee_per_batch',
])]
class ProductSku extends Model
{
    protected function casts(): array
    {
        return [
            'weight' => 'decimal:3',
            'length' => 'decimal:3',
            'width' => 'decimal:3',
            'height' => 'decimal:3',
            'transfer_batch_quantity' => 'decimal:3',
            'transfer_fee_per_batch' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ShopSkuPrice::class);
    }

    public function getRouteKeyName(): string
    {
        return 'sku';
    }
}
