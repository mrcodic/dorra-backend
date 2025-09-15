<?php

namespace App\Http\Resources;

use App\Http\Resources\Product\ProductPriceResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductSpecificationResource;
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
            'has_custom_prices' =>$this->has_custom_prices,
            'base_price' => $this->base_price,
            'custom_prices' => ProductPriceResource::collection($this->whenLoaded('prices')),
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl("categories")),
            'main_image' => $this->whenLoaded('media', function () {
                return MediaResource::make($this->getFirstMedia('categories'));
            }),
            'all_product_images' => $this->whenLoaded('media', function () {
                return MediaResource::collection($this->getMedia('category_extra_images'));
            }),
            'specs' => ProductSpecificationResource::collection($this->whenLoaded('specifications')),
            'dimensions' => DimensionResource::collection($this->whenLoaded('dimensions')),
            'product_model_image' => $this->getFirstMediaUrl('category_model_image'),
            'sub_categories' => CategoryResource::collection($this->whenLoaded('landingSubCategories')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'category_products' => ProductResource::collection($this->whenLoaded('landingProducts')),
            'sub_category_products' => ProductResource::collection($this->whenLoaded('products')),
            'is_has_category' => $this->is_has_category,
            'show_add_cart_btn' => $this->show_add_cart_btn,
            'type' => 'category',
            'rating' => $this->when(isset($this->rating), $this->rating),
            'reviews_count' => $this->when(isset($this->reviews_count), $this->reviews_count),

        ];
    }
}
