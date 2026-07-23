<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(IndexProductRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $now = now();

        $bestOfferSubquery = DB::table('shop_sku_prices')
            ->selectRaw(
                'MIN(CASE
                    WHEN shop_sku_promotions.id IS NOT NULL
                    THEN shop_sku_promotions.promotional_price
                    ELSE shop_sku_prices.price
                END)'
            )
            ->join(
                'product_skus',
                'product_skus.id',
                '=',
                'shop_sku_prices.product_sku_id',
            )
            ->join('warehouses', 'warehouses.shop_id', '=', 'shop_sku_prices.shop_id')
            ->join('stocks', function ($join): void {
                $join->on('stocks.warehouse_id', '=', 'warehouses.id')
                    ->on('stocks.product_sku_id', '=', 'product_skus.id')
                    ->whereColumn('stocks.quantity_on_hand', '>', 'stocks.quantity_reserved');
            })
            ->leftJoin('shop_sku_promotions', function ($join) use ($now): void {
                $join->on(
                    'shop_sku_promotions.shop_sku_price_id',
                    '=',
                    'shop_sku_prices.id',
                )
                    ->whereNull('shop_sku_promotions.cancelled_at')
                    ->where('shop_sku_promotions.starts_at', '<=', $now)
                    ->where(function ($query) use ($now): void {
                        $query->whereNull('shop_sku_promotions.ends_at')
                            ->orWhere('shop_sku_promotions.ends_at', '>=', $now);
                    })
                    ->whereRaw(
                        'shop_sku_promotions.quantity_limit > '
                        .'shop_sku_promotions.quantity_reserved + shop_sku_promotions.quantity_sold'
                    )
                    ->whereColumn(
                        'shop_sku_promotions.promotional_price',
                        '<',
                        'shop_sku_prices.price',
                    );
            })
            ->whereColumn('product_skus.product_id', 'products.id');

        $query = Product::query()
            ->select('products.*')
            ->addSelect(['best_offer_price' => $bestOfferSubquery])
            ->with($this->catalogRelations())
            ->when($filters['category'] ?? null, function ($query, string $slug): void {
                $category = Category::where('slug', $slug)->firstOrFail();

                $query->whereHas(
                    'subcategories',
                    fn ($query) => $query->where('subcategories.category_id', $category->id),
                );
            })
            ->when($filters['subcategory'] ?? null, function ($query, string $slug): void {
                $subcategory = Subcategory::query()
                    ->with('children:id,parent_id')
                    ->where('slug', $slug)
                    ->firstOrFail();

                $subcategoryIds = $subcategory->children
                    ->pluck('id')
                    ->prepend($subcategory->id);

                $query->whereHas(
                    'subcategories',
                    fn ($query) => $query->whereIn('subcategories.id', $subcategoryIds),
                );
            })
            ->when(
                $filters['brand'] ?? null,
                fn ($query, string $slug) => $query->whereHas(
                    'brand',
                    fn ($query) => $query->where('slug', $slug),
                ),
            )
            ->when(
                $filters['search'] ?? null,
                fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('skus', fn ($query) => $query
                            ->where('sku', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%"));
                }),
            )
            ->when(
                $filters['in_stock'] ?? false,
                fn ($query) => $query->whereHas(
                    'skus.stocks',
                    fn ($query) => $query->whereColumn(
                        'quantity_on_hand',
                        '>',
                        'quantity_reserved',
                    ),
                ),
            );

        if (($filters['sort'] ?? 'best_offer') === 'name') {
            $query->orderBy('name');
        } else {
            $query->orderByRaw('best_offer_price IS NULL')
                ->orderBy('best_offer_price')
                ->orderBy('name');
        }

        $products = $query->paginate(15)->withQueryString();

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $subcategoryIds = $data['subcategory_ids'] ?? [];
        unset($data['subcategory_ids']);

        $data['slug'] = $this->uniqueSlug($data['name']);

        $product = DB::transaction(function () use ($data, $subcategoryIds): Product {
            $product = Product::create($data);
            $product->subcategories()->sync($subcategoryIds);

            return $product;
        });

        $product->load($this->catalogRelations());

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        $product->load($this->catalogRelations());

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $data = $request->validated();
        $hasSubcategories = array_key_exists('subcategory_ids', $data);
        $subcategoryIds = $data['subcategory_ids'] ?? [];
        unset($data['subcategory_ids']);

        if (array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug($data['name'], $product);
        }

        DB::transaction(function () use (
            $product,
            $data,
            $hasSubcategories,
            $subcategoryIds,
        ): void {
            $product->update($data);

            if ($hasSubcategories) {
                $product->subcategories()->sync($subcategoryIds);
            }
        });

        $product->load($this->catalogRelations());

        return new ProductResource($product);
    }

    public function destroy(Product $product): Response
    {
        abort_if(
            $product->skus()->exists(),
            409,
            'Nao e possivel excluir um produto que possui SKUs.',
        );

        $product->delete();

        return response()->noContent();
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

    private function uniqueSlug(string $name, ?Product $ignoredProduct = null): string
    {
        $baseSlug = Str::slug($name) ?: 'produto';
        $slug = $baseSlug;
        $suffix = 2;

        while (Product::query()
            ->where('slug', $slug)
            ->when(
                $ignoredProduct,
                fn ($query) => $query->whereKeyNot($ignoredProduct->getKey()),
            )
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
