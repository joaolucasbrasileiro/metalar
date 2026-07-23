<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductSkuRequest;
use App\Http\Requests\UpdateProductSkuRequest;
use App\Http\Resources\ProductSkuResource;
use App\Models\ProductSku;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductSkuController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $skus = ProductSku::query()
            ->with($this->catalogRelations())
            ->orderBy('sku')
            ->paginate(15);

        return ProductSkuResource::collection($skus);
    }

    public function store(StoreProductSkuRequest $request): JsonResponse
    {
        $productSku = ProductSku::create($request->validated());
        $productSku->load($this->catalogRelations());

        return (new ProductSkuResource($productSku))
            ->response()
            ->setStatusCode(201);
    }

    public function show(ProductSku $productSku): ProductSkuResource
    {
        $productSku->load($this->catalogRelations());

        return new ProductSkuResource($productSku);
    }

    public function update(
        UpdateProductSkuRequest $request,
        ProductSku $productSku,
    ): ProductSkuResource {
        $productSku->update($request->validated());
        $productSku->load($this->catalogRelations());

        return new ProductSkuResource($productSku);
    }

    public function destroy(ProductSku $productSku): JsonResponse|Response
    {
        if ($productSku->stocks()->exists() || $productSku->prices()->exists()) {
            return response()->json([
                'message' => 'Nao e possivel excluir um SKU que possui estoque ou precos.',
            ], 409);
        }

        $productSku->delete();

        return response()->noContent();
    }

    private function catalogRelations(): array
    {
        return [
            'product',
            'prices.shop',
            'prices.promotions',
            'stocks.warehouse.shop',
        ];
    }
}
