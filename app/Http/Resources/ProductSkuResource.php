<?php

namespace App\Http\Resources;

use App\Models\ShopSkuPrice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ProductSkuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $offers = $this->relationLoaded('prices') && $this->relationLoaded('stocks')
            ? $this->offers()
            : collect();

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'variant_name' => $this->variant_name,
            'unit' => $this->unit,
            'weight' => $this->weight,
            'dimensions' => [
                'length' => $this->length,
                'width' => $this->width,
                'height' => $this->height,
            ],
            'transfer' => [
                'batch_quantity' => $this->transfer_batch_quantity,
                'fee_per_batch' => $this->transfer_fee_per_batch,
                'minimum_fee' => number_format(
                    (float) config('commerce.internal_transfer_min_fee'),
                    2,
                    '.',
                    '',
                ),
            ],
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
            ]),
            'total_available' => $this->when(
                $this->relationLoaded('stocks'),
                fn () => number_format(
                    $this->stocks->sum(
                        fn ($stock) => max(
                            0,
                            (float) $stock->quantity_on_hand
                                - (float) $stock->quantity_reserved,
                        )
                    ),
                    3,
                    '.',
                    '',
                ),
            ),
            'best_offer' => $offers->first(),
            'offers' => $offers->values(),
        ];
    }

    private function offers(): Collection
    {
        return $this->prices
            ->map(function (ShopSkuPrice $price): ?array {
                $stock = $this->stocks->first(
                    fn ($stock) => $stock->warehouse?->shop_id === $price->shop_id
                );

                $available = $stock
                    ? max(0, (float) $stock->quantity_on_hand - (float) $stock->quantity_reserved)
                    : 0;

                if ($available <= 0) {
                    return null;
                }

                $promotion = $price->promotions
                    ->filter(fn ($promotion) => $promotion->isActive())
                    ->sortBy(fn ($promotion) => (float) $promotion->promotional_price)
                    ->first();

                $promotionAvailable = $promotion
                    ? min($available, $promotion->remainingQuantity())
                    : 0;

                $isPromotion = $promotion
                    && $promotionAvailable > 0
                    && (float) $promotion->promotional_price < (float) $price->price;

                return [
                    'shop' => [
                        'id' => $price->shop->id,
                        'code' => $price->shop->code,
                        'name' => $price->shop->name,
                    ],
                    'regular_price' => $price->price,
                    'effective_price' => $isPromotion
                        ? $promotion->promotional_price
                        : $price->price,
                    'is_promotion' => (bool) $isPromotion,
                    'promotion_id' => $isPromotion ? $promotion->id : null,
                    'available_quantity' => number_format(
                        $isPromotion ? $promotionAvailable : $available,
                        3,
                        '.',
                        '',
                    ),
                    'total_shop_stock' => number_format($available, 3, '.', ''),
                    'promotion_ends_at' => $isPromotion
                        ? $promotion->ends_at?->toISOString()
                        : null,
                ];
            })
            ->filter()
            ->sortBy([
                ['effective_price', 'asc'],
                ['is_promotion', 'desc'],
            ])
            ->values();
    }
}
