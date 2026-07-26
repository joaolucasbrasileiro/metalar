<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class FavoriteProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $user = Auth::guard('api')->user();

        $products = $user->favoriteProducts()
            ->with($this->catalogRelations())
            ->orderByPivot('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return ProductResource::collection($products);
    }

    public function store(Product $product): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $alreadyFavorited = $user->favoriteProducts()
            ->whereKey($product->getKey())
            ->exists();

        $user->favoriteProducts()->syncWithoutDetaching([$product->id]);

        return response()->json([
            'message' => $alreadyFavorited
                ? 'Produto ja estava nos favoritos.'
                : 'Produto adicionado aos favoritos.',
            'is_favorite' => true,
        ], $alreadyFavorited ? 200 : 201);
    }

    public function destroy(Product $product): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $user->favoriteProducts()->detach($product->id);

        return response()->json([
            'message' => 'Produto removido dos favoritos.',
            'is_favorite' => false,
        ]);
    }

    private function catalogRelations(): array
    {
        return [
            'brand',
            'subcategories.category',
            'subcategories.parent',
            'images',
            'skus.prices.shop',
            'skus.prices.promotions',
            'skus.stocks.warehouse.shop',
        ];
    }
}
