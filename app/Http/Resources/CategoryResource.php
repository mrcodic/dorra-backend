<?php

namespace App\Http\Resources;

use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->when(isset($this->id), $this->id),
            'name' => $this->when(isset($this->name), $this->name),
            'description' => $this->description,
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl("categories")),
            'sub_categories' => CategoryResource::collection($this->whenLoaded('children')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'category_products' => ProductResource::collection($this->whenLoaded('landingProducts')),
            'sub_category_products' => ProductResource::collection($this->whenLoaded('landingSubCategories')),
            'is_has_category' => $this->is_has_category,
            'show_add_cart_btn' => $this->show_add_cart_btn,
        ];
    }
}
