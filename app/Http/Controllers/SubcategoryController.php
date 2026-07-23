<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubcategoryRequest;
use App\Http\Requests\UpdateSubcategoryRequest;
use App\Http\Resources\SubcategoryResource;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class SubcategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $subcategories = Subcategory::query()
            ->whereNull('parent_id')
            ->with([
                'category',
                'children' => fn ($query) => $query->withCount('products')->orderBy('name'),
            ])
            ->withCount(['children', 'products'])
            ->orderBy('name')
            ->paginate(15);

        return SubcategoryResource::collection($subcategories);
    }

    public function store(StoreSubcategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['name']);

        $subcategory = Subcategory::create($data);
        $subcategory->load(['category', 'parent'])->loadCount(['children', 'products']);

        return (new SubcategoryResource($subcategory))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Subcategory $subcategory): SubcategoryResource
    {
        $subcategory->load([
            'category',
            'parent',
            'children' => fn ($query) => $query->withCount('products')->orderBy('name'),
        ])->loadCount(['children', 'products']);

        return new SubcategoryResource($subcategory);
    }

    public function update(
        UpdateSubcategoryRequest $request,
        Subcategory $subcategory,
    ): SubcategoryResource {
        $data = $request->validated();

        if (array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug($data['name'], $subcategory);
        }

        $subcategory->update($data);
        $subcategory->load(['category', 'parent', 'children'])
            ->loadCount(['children', 'products']);

        return new SubcategoryResource($subcategory);
    }

    public function destroy(Subcategory $subcategory): JsonResponse|Response
    {
        if ($subcategory->children()->exists()) {
            return response()->json([
                'message' => 'Nao e possivel excluir uma subcategoria que possui filhas.',
            ], 409);
        }

        if ($subcategory->products()->exists()) {
            return response()->json([
                'message' => 'Nao e possivel excluir uma subcategoria que possui produtos.',
            ], 409);
        }

        $subcategory->delete();

        return response()->noContent();
    }

    private function uniqueSlug(string $name, ?Subcategory $ignoredSubcategory = null): string
    {
        $baseSlug = Str::slug($name) ?: 'subcategoria';
        $slug = $baseSlug;
        $suffix = 2;

        while (Subcategory::query()
            ->where('slug', $slug)
            ->when(
                $ignoredSubcategory,
                fn ($query) => $query->whereKeyNot($ignoredSubcategory->getKey()),
            )
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
