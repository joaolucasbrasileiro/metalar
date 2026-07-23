<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class BrandController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $brands = Brand::query()
            ->withCount('products')
            ->orderBy('name')
            ->paginate(15);

        return BrandResource::collection($brands);
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        $data = $request->safe()->except('logo');
        $data['slug'] = $this->uniqueSlug($data['name']);

        $logoPath = $request->file('logo')->store('brands/logos', 'public');
        $data['logo_path'] = $logoPath;

        try {
            $brand = Brand::create($data);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($logoPath);

            throw $exception;
        }

        $brand->loadCount('products');

        return (new BrandResource($brand))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Brand $brand): BrandResource
    {
        $brand->loadCount('products');

        return new BrandResource($brand);
    }

    public function update(UpdateBrandRequest $request, Brand $brand): BrandResource
    {
        $data = $request->safe()->except('logo');

        if (array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug($data['name'], $brand);
        }

        $oldLogoPath = $brand->logo_path;
        $newLogoPath = null;

        if ($request->hasFile('logo')) {
            $newLogoPath = $request->file('logo')->store('brands/logos', 'public');
            $data['logo_path'] = $newLogoPath;
        }

        try {
            $brand->update($data);
        } catch (Throwable $exception) {
            if ($newLogoPath) {
                Storage::disk('public')->delete($newLogoPath);
            }

            throw $exception;
        }

        if ($newLogoPath && $oldLogoPath) {
            Storage::disk('public')->delete($oldLogoPath);
        }

        $brand->loadCount('products');

        return new BrandResource($brand);
    }

    public function destroy(Brand $brand): JsonResponse|Response
    {
        if ($brand->products()->exists()) {
            return response()->json([
                'message' => 'Nao e possivel excluir uma marca que possui produtos.',
            ], 409);
        }

        $logoPath = $brand->logo_path;

        $brand->delete();

        if ($logoPath) {
            Storage::disk('public')->delete($logoPath);
        }

        return response()->noContent();
    }

    private function uniqueSlug(string $name, ?Brand $ignoredBrand = null): string
    {
        $baseSlug = Str::slug($name) ?: 'marca';
        $slug = $baseSlug;
        $suffix = 2;

        while (Brand::query()
            ->where('slug', $slug)
            ->when(
                $ignoredBrand,
                fn ($query) => $query->whereKeyNot($ignoredBrand->getKey()),
            )
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
