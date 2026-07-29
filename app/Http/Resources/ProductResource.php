<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'specifications' => $this->specifications ?? [],
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'subcategories' => SubcategoryResource::collection(
                $this->whenLoaded('subcategories')
            ),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'skus' => ProductSkuResource::collection($this->whenLoaded('skus')),
            'sales_rank' => $this->when(
                $this->hasAttribute('sales_rank'),
                $this->sales_rank,
            ),
            'sold_quantity' => $this->when(
                $this->hasAttribute('sold_quantity'),
                $this->decimalAttribute('sold_quantity', 3),
            ),
            'sold_revenue' => $this->when(
                $this->hasAttribute('sold_revenue'),
                $this->decimalAttribute('sold_revenue', 2),
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function hasAttribute(string $attribute): bool
    {
        return array_key_exists($attribute, $this->resource->getAttributes());
    }

    private function decimalAttribute(string $attribute, int $decimals): string
    {
        return number_format((float) $this->{$attribute}, $decimals, '.', '');
    }
}
