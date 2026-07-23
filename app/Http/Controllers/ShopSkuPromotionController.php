<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShopSkuPromotionRequest;
use App\Http\Resources\ShopSkuPromotionResource;
use App\Models\ProductSku;
use App\Models\Shop;
use App\Models\ShopSkuPromotion;
use App\Services\ShopSkuPromotionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class ShopSkuPromotionController extends Controller
{
    public function index(Shop $shop): AnonymousResourceCollection
    {
        $promotions = ShopSkuPromotion::query()
            ->whereHas('shopSkuPrice', fn ($query) => $query
                ->where('shop_id', $shop->id))
            ->with(['createdBy', 'shopSkuPrice.productSku'])
            ->latest()
            ->paginate(20);

        return ShopSkuPromotionResource::collection($promotions);
    }

    public function store(
        StoreShopSkuPromotionRequest $request,
        Shop $shop,
        ProductSku $productSku,
        ShopSkuPromotionService $service,
    ): JsonResponse {
        $promotion = $service->create(
            $shop,
            $productSku,
            $request->validated(),
            Auth::guard('api')->user(),
        );

        return (new ShopSkuPromotionResource($promotion))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(
        Shop $shop,
        ShopSkuPromotion $promotion,
        ShopSkuPromotionService $service,
    ): ShopSkuPromotionResource {
        return new ShopSkuPromotionResource($service->cancel($shop, $promotion));
    }
}
