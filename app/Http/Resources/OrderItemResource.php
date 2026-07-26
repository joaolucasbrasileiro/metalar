<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_sku_id' => $this->product_sku_id,
            'shop_id' => $this->shop_id,
            'promotion_id' => $this->shop_sku_promotion_id,
            'quantity' => $this->quantity,
            'regular_unit_price' => $this->regular_unit_price,
            'unit_price' => $this->unit_price,
            'discount_total' => $this->discount_total,
            'total' => $this->total,
            'snapshot' => [
                'product_name' => $this->product_name,
                'product_sku' => $this->product_sku,
                'shop_name' => $this->shop_name,
            ],
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
            ]),
            'shop' => $this->whenLoaded('shop', fn () => [
                'id' => $this->shop->id,
                'code' => $this->shop->code,
                'name' => $this->shop->name,
            ]),
        ];
    }
}
