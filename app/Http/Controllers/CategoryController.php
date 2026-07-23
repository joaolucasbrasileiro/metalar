<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->with([
                'rootSubcategories' => fn ($query) => $query
                    ->withCount(['children', 'products'])
                    ->with([
                        'children' => fn ($query) => $query->withCount('products'),
                    ])
                    ->orderBy('name'),
            ])
            ->withCount('subcategories')
            ->orderBy('name')
            ->paginate(15);

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['name']);

        $category = Category::create($data);
        $category->loadCount('subcategories');

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Category $category): CategoryResource
    {
        $category->load([
            'rootSubcategories' => fn ($query) => $query
                ->withCount(['children', 'products'])
                ->with([
                    'children' => fn ($query) => $query->withCount('products'),
                ])
                ->orderBy('name'),
        ])->loadCount('subcategories');

        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $data = $request->validated();

        if (array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug($data['name'], $category);
        }

        $category->update($data);
        $category->load([
            'rootSubcategories.children',
        ])->loadCount('subcategories');

        return new CategoryResource($category);
    }

    public function destroy(Category $category): JsonResponse|Response
    {
        if ($category->subcategories()->exists()) {
            return response()->json([
                'message' => 'Nao e possivel excluir uma categoria que possui subcategorias.',
            ], 409);
        }

        $category->delete();

        return response()->noContent();
    }

    private function uniqueSlug(string $name, ?Category $ignoredCategory = null): string
    {
        $baseSlug = Str::slug($name) ?: 'categoria';
        $slug = $baseSlug;
        $suffix = 2;

        while (Category::query()
            ->where('slug', $slug)
            ->when(
                $ignoredCategory,
                fn ($query) => $query->whereKeyNot($ignoredCategory->getKey()),
            )
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
