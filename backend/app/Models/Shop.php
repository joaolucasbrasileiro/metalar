<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
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

    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
