<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopSkuPromotionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'promotional_price' => $this->promotional_price,
            'quantity_limit' => $this->quantity_limit,
            'quantity_reserved' => $this->quantity_reserved,
            'quantity_sold' => $this->quantity_sold,
            'quantity_remaining' => number_format($this->remainingQuantity(), 3, '.', ''),
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'is_active' => $this->isActive(),
            'product_sku' => $this->whenLoaded('shopSkuPrice', fn () => $this->shopSkuPrice->relationLoaded('productSku') ? [
                'id' => $this->shopSkuPrice->productSku->id,
                'sku' => $this->shopSkuPrice->productSku->sku,
            ] : null),
            'created_by' => $this->whenLoaded('createdBy', fn () => [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ]),
        ];
    }
}
