<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\DimensionResource;
use App\Http\Resources\IndustryResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\OfferResource;
use App\Http\Resources\TagResource;
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
            'id' => $this->when(isset($this->id), $this->id),
            'name' => $this->when(isset($this->name), $this->name),
            'description' => $this->when(isset($this->description), $this->description),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'has_custom_prices' => $this->when(isset($this->has_custom_prices), $this->has_custom_prices),
            'base_price' => $this->when(isset($this->base_price), $this->base_price),
            'custom_prices' => ProductPriceResource::collection($this->whenLoaded('prices')),
            'rating' => $this->when(isset($this->rating), $this->rating),
            'reviews_count' => $this->when(isset($this->reviews_count), $this->reviews_count),
            'type' => 'product',
            'main_image' => $this->whenLoaded('media', function () {
                return MediaResource::make($this->getFirstMedia('product_main_image'));
            }),
            'all_product_images' => $this->whenLoaded('media', function () {
                return MediaResource::collection($this->getAllProductImages());
            }),
            'template_tags' => $this->whenLoaded('templates', function () {
                $this->templates->loadMissing('tags');
                $tags = $this->templates
                    ->pluck('tags')
                    ->flatten()
                    ->unique('id')
                    ->values();

                return TagResource::collection($tags);
            }),
            'template_industries' => $this->whenLoaded('templates', function () {
                $this->templates->loadMissing('industries.parent');
                $all = $this->templates->pluck('industries')->flatten();
                $unique = $all->unique('id')->values();
                $parents = $unique
                    ->map(fn ($ind) => $ind->parent_id ? $ind->parent : $ind)
                    ->filter()
                    ->unique('id')
                    ->values();

                $parents = $parents->map(function ($parent) use ($unique) {
                    $children = $unique
                        ->where('parent_id', $parent->id)
                        ->values();

                    $parent->setRelation('children', $children);

                    return $parent;
                });

                return IndustryResource::collection($parents);
            }),

            'dimensions' => DimensionResource::collection($this->whenLoaded('dimensions')),
            'offer' => OfferResource::make($this->whenLoaded('lastOffer')),
            'specs' => ProductSpecificationResource::collection($this->whenLoaded('specifications')),
            'is_saved' => $this->when(
                $this->relationLoaded('saves') && is_null($this->deleted_at),
                fn() => $this->saves->contains(fn($save) => $save->user_id === auth('sanctum')->id())
            ),
            'has_mockup' => (boolean) $this->has_mockup,
            'product_model_image' => $this->getFirstMediaUrl('product_model_image'),
            'show_add_cart_btn' => $this->show_add_cart_btn,
            'show_customize_design_btn' => $this->show_customize_design_btn,


        ];
    }
}
