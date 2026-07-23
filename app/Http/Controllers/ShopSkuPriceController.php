<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertShopSkuPriceRequest;
use App\Http\Resources\ShopSkuPriceResource;
use App\Models\ProductSku;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShopSkuPriceController extends Controller
{
    public function index(Shop $shop): AnonymousResourceCollection
    {
        $prices = $shop->skuPrices()
            ->with(['shop', 'productSku.product', 'promotions.createdBy'])
            ->orderBy('product_sku_id')
            ->paginate(20);

        return ShopSkuPriceResource::collection($prices);
    }

    public function update(
        UpsertShopSkuPriceRequest $request,
        Shop $shop,
        ProductSku $productSku,
    ): JsonResponse {
        $price = $shop->skuPrices()->updateOrCreate(
            ['product_sku_id' => $productSku->id],
            ['price' => $request->validated('price')],
        );

        $price->load(['shop', 'promotions.createdBy']);

        return (new ShopSkuPriceResource($price))->response();
    }

    public function destroy(Shop $shop, ProductSku $productSku): JsonResponse
    {
        $price = $shop->skuPrices()
            ->where('product_sku_id', $productSku->id)
            ->firstOrFail();

        if ($price->promotions()->whereNull('cancelled_at')->exists()) {
            return response()->json([
                'message' => 'Cancele as promocoes antes de excluir o preco.',
            ], 409);
        }

        $price->delete();

        return response()->json(null, 204);
    }
}
