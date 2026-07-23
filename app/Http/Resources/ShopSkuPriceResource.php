<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopSkuPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'shop' => $this->whenLoaded('shop', fn () => [
                'id' => $this->shop->id,
                'code' => $this->shop->code,
                'name' => $this->shop->name,
            ]),
            'product_sku_id' => $this->product_sku_id,
            'product_sku' => $this->whenLoaded('productSku', fn () => [
                'id' => $this->productSku->id,
                'sku' => $this->productSku->sku,
                'unit' => $this->productSku->unit,
            ]),
            'promotions' => ShopSkuPromotionResource::collection(
                $this->whenLoaded('promotions')
            ),
        ];
    }
}
