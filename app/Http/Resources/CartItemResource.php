<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_sku_id' => $this->product_sku_id,
            'shop_id' => $this->shop_id,
            'promotion_id' => $this->shop_sku_promotion_id,
            'quantity' => $this->quantity,
            'product_sku' => $this->whenLoaded('productSku', fn () => [
                'id' => $this->productSku->id,
                'sku' => $this->productSku->sku,
                'unit' => $this->productSku->unit,
                'product' => $this->productSku->relationLoaded('product') ? [
                    'id' => $this->productSku->product->id,
                    'name' => $this->productSku->product->name,
                    'slug' => $this->productSku->product->slug,
                ] : null,
            ]),
            'shop' => $this->whenLoaded('shop', fn () => [
                'id' => $this->shop->id,
                'code' => $this->shop->code,
                'name' => $this->shop->name,
            ]),
            'promotion' => $this->whenLoaded('promotion', fn () => $this->promotion ? [
                'id' => $this->promotion->id,
                'promotional_price' => $this->promotion->promotional_price,
                'quantity_remaining' => number_format($this->promotion->remainingQuantity(), 3, '.', ''),
                'is_active' => $this->promotion->isActive(),
            ] : null),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
