<?php

namespace App\Services;

use App\Models\ProductSku;
use App\Models\Shop;
use App\Models\ShopSkuPrice;
use Illuminate\Support\Facades\DB;

class ShopSkuPriceService
{
    public function upsert(Shop $shop, ProductSku $productSku, float $price): ShopSkuPrice
    {
        return DB::transaction(function () use ($shop, $productSku, $price): ShopSkuPrice {
            $shop->skuPrices()
                ->where('product_sku_id', $productSku->id)
                ->lockForUpdate()
                ->first();

            $skuPrice = $shop->skuPrices()->updateOrCreate(
                ['product_sku_id' => $productSku->id],
                ['price' => $price],
            );

            return $skuPrice->load(['shop', 'promotions.createdBy']);
        });
    }

    public function delete(Shop $shop, ProductSku $productSku): bool
    {
        return DB::transaction(function () use ($shop, $productSku): bool {
            $price = $shop->skuPrices()
                ->where('product_sku_id', $productSku->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($price->promotions()->whereNull('cancelled_at')->lockForUpdate()->get()->isNotEmpty()) {
                return false;
            }

            $price->delete();

            return true;
        });
    }
}
