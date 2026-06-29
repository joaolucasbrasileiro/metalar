<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['brand_id', 'name', 'slug', 'description'])]
class Product extends Model
{
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
