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
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'subcategories' => SubcategoryResource::collection(
                $this->whenLoaded('subcategories')
            ),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'skus' => ProductSkuResource::collection($this->whenLoaded('skus')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
