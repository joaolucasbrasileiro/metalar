<?php

namespace App\Services;

use App\Models\ProductSku;
use App\Models\Shop;
use App\Models\ShopSkuPromotion;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShopSkuPromotionService
{
    public function create(
        Shop $shop,
        ProductSku $productSku,
        array $data,
        User $user,
    ): ShopSkuPromotion {
        return DB::transaction(function () use (
            $shop,
            $productSku,
            $data,
            $user,
        ): ShopSkuPromotion {
            $price = $shop->skuPrices()
                ->where('product_sku_id', $productSku->id)
                ->lockForUpdate()
                ->firstOrFail();

            $stock = $shop->warehouse()->firstOrFail()->stocks()
                ->where('product_sku_id', $productSku->id)
                ->lockForUpdate()
                ->first();

            $available = $stock
                ? max(0, (float) $stock->quantity_on_hand - (float) $stock->quantity_reserved)
                : 0;

            if ((float) $data['promotional_price'] >= (float) $price->price) {
                throw ValidationException::withMessages([
                    'promotional_price' => 'O preco promocional deve ser menor que o preco normal.',
                ]);
            }

            if ((float) $data['quantity_limit'] > $available) {
                throw ValidationException::withMessages([
                    'quantity_limit' => 'A quantidade promocional nao pode superar o estoque disponivel.',
                ]);
            }

            $hasOpenPromotion = $price->promotions()
                ->whereNull('cancelled_at')
                ->lockForUpdate()
                ->get()
                ->contains(fn ($promotion) => $promotion->remainingQuantity() > 0
                    && ($promotion->ends_at === null || $promotion->ends_at >= now()));

            if ($hasOpenPromotion) {
                throw ValidationException::withMessages([
                    'promotional_price' => 'Ja existe uma promocao aberta para este SKU nesta loja.',
                ]);
            }

            $startsAt = isset($data['starts_at'])
                ? Carbon::parse($data['starts_at'])
                : now();

            if (isset($data['ends_at']) && Carbon::parse($data['ends_at']) <= $startsAt) {
                throw ValidationException::withMessages([
                    'ends_at' => 'O termino deve ser posterior ao inicio da promocao.',
                ]);
            }

            return $price->promotions()
                ->create([
                    'created_by_user_id' => $user->id,
                    'promotional_price' => $data['promotional_price'],
                    'quantity_limit' => $data['quantity_limit'],
                    'starts_at' => $startsAt,
                    'ends_at' => $data['ends_at'] ?? null,
                ])
                ->load('createdBy');
        });
    }

    public function cancel(Shop $shop, ShopSkuPromotion $promotion): ShopSkuPromotion
    {
        return DB::transaction(function () use ($shop, $promotion): ShopSkuPromotion {
            $lockedPromotion = ShopSkuPromotion::query()
                ->lockForUpdate()
                ->findOrFail($promotion->id);

            abort_unless(
                $lockedPromotion->shopSkuPrice()->where('shop_id', $shop->id)->exists(),
                404,
            );

            $lockedPromotion->update(['cancelled_at' => now()]);

            return $lockedPromotion->load('createdBy');
        });
    }
}
