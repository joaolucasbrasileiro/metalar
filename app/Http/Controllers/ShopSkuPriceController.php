<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertShopSkuPriceRequest;
use App\Http\Resources\ShopSkuPriceResource;
use App\Models\ProductSku;
use App\Models\Shop;
use App\Services\ShopSkuPriceService;
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
        ShopSkuPriceService $service,
    ): JsonResponse {
        $price = $service->upsert(
            $shop,
            $productSku,
            (float) $request->validated('price'),
        );

        return (new ShopSkuPriceResource($price))->response();
    }

    public function destroy(
        Shop $shop,
        ProductSku $productSku,
        ShopSkuPriceService $service,
    ): JsonResponse {
        if (! $service->delete($shop, $productSku)) {
            return response()->json([
                'message' => 'Cancele as promocoes antes de excluir o preco.',
            ], 409);
        }

        return response()->json(null, 204);
    }
}
