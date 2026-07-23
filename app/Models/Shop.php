<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'name',
    'cnpj',
    'phone',
    'code',
    'zip_code',
    'street',
    'number',
    'complement',
    'neighborhood',
    'city',
    'state',
])]
class Shop extends Model
{
    public function warehouse(): HasOne
    {
        return $this->hasOne(Warehouse::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function skuPrices(): HasMany
    {
        return $this->hasMany(ShopSkuPrice::class);
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
