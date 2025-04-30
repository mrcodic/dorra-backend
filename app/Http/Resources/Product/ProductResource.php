<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\MediaResource;
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
            'description' => $this->description,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'has_custom_prices' => $this->when($this->has_custom_prices,true,false),
            'base_price' => $this->base_price,
            'custom_prices' =>ProductPriceResource::collection($this->whenLoaded('prices')),
            'rating' => $this->rating,
            'reviews_count' => $this->reviews_count,
            'main_image' => MediaResource::make($this->getFirstMedia('product_main_image')),
            'all_product_images' => MediaResource::collection($this->getAllProductImages()),
        ];
    }
}
