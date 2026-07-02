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

        $query = Product::query()
            ->with(['brand', 'subcategories.category', 'subcategories.parent'])
            ->orderBy('name')
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
            });

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

        $product->load(['brand', 'subcategories.category', 'subcategories.parent']);

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        $product->load(['brand', 'subcategories.category', 'subcategories.parent']);

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

        $product->load(['brand', 'subcategories.category', 'subcategories.parent']);

        return new ProductResource($product);
    }

    public function destroy(Product $product): Response
    {
        $product->delete();

        return response()->noContent();
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
