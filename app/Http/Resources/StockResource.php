<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity_on_hand' => $this->quantity_on_hand,
            'quantity_reserved' => $this->quantity_reserved,
            'quantity_available' => number_format(
                (float) $this->quantity_on_hand - (float) $this->quantity_reserved,
                3,
                '.',
                '',
            ),
            'warehouse' => $this->whenLoaded('warehouse', fn () => [
                'id' => $this->warehouse->id,
                'name' => $this->warehouse->name,
                'shop' => $this->warehouse->relationLoaded('shop') ? [
                    'id' => $this->warehouse->shop->id,
                    'code' => $this->warehouse->shop->code,
                    'name' => $this->warehouse->shop->name,
                ] : null,
            ]),
            'product_sku' => $this->whenLoaded('productSku', fn () => [
                'id' => $this->productSku->id,
                'sku' => $this->productSku->sku,
                'unit' => $this->productSku->unit,
            ]),
            'movements' => StockMovementResource::collection($this->whenLoaded('movements')),
        ];
    }
}
