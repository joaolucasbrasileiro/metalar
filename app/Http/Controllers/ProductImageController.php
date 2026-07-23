<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductImageRequest;
use App\Http\Resources\ProductImageResource;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductImageController extends Controller
{
    public function store(StoreProductImageRequest $request): JsonResponse
    {
        $data = $request->safe()->except('image');
        $product = Product::findOrFail($data['product_id']);
        $imagePath = $request->file('image')->store('products', 'public');

        try {
            $productImage = DB::transaction(function () use (
                $product,
                $data,
                $imagePath,
            ): ProductImage {
                $isPrimary = (bool) ($data['is_primary'] ?? false)
                    || ! $product->images()->exists();

                if ($isPrimary) {
                    $product->images()->update(['is_primary' => false]);
                }

                return $product->images()->create([
                    'image_path' => $imagePath,
                    'alt_text' => $data['alt_text'] ?? null,
                    'position' => $data['position'] ?? 0,
                    'is_primary' => $isPrimary,
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($imagePath);

            throw $exception;
        }

        return (new ProductImageResource($productImage))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(ProductImage $productImage): Response
    {
        $productId = $productImage->product_id;
        $wasPrimary = $productImage->is_primary;
        $imagePath = $productImage->image_path;

        DB::transaction(function () use ($productImage, $productId, $wasPrimary): void {
            $productImage->delete();

            if ($wasPrimary) {
                ProductImage::where('product_id', $productId)
                    ->orderBy('position')
                    ->orderBy('id')
                    ->first()?->update(['is_primary' => true]);
            }
        });

        Storage::disk('public')->delete($imagePath);

        return response()->noContent();
    }
}
