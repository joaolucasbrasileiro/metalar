<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubcategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'level' => $this->parent_id === null ? 1 : 2,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'parent' => new SubcategoryResource($this->whenLoaded('parent')),
            'children_count' => $this->whenCounted('children'),
            'products_count' => $this->whenCounted('products'),
            'children' => SubcategoryResource::collection($this->whenLoaded('children')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
