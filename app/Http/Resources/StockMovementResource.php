<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'quantity_before' => $this->quantity_before,
            'quantity_after' => $this->quantity_after,
            'reason' => $this->reason,
            'stock' => $this->whenLoaded('stock', fn () => [
                'id' => $this->stock->id,
                'product_sku' => $this->stock->relationLoaded('productSku') ? [
                    'id' => $this->stock->productSku->id,
                    'sku' => $this->stock->productSku->sku,
                ] : null,
            ]),
            'user' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
