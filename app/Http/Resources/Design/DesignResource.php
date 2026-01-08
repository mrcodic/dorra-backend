<?php

namespace App\Http\Resources\Design;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\DimensionResource;
use App\Http\Resources\Product\ProductPriceResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductSpecificationOption;
use App\Http\Resources\Product\ProductSpecificationResource;
use App\Http\Resources\Template\TemplateResource;
use App\Http\Resources\UserResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpecification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DesignResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $designable = $this->designable;
        return [
            'id' => $this->id,
            'name'=> $this->name,
            'description'=> $this->description,
            'design_data' => $this->when(request('design_data') == true, $this->design_data),
            'design_back_data' => $this->when(request('design_data') == true, $this->design_back_data),
            'design_image' => $this->getFirstMediaUrl('designs'),
            'source_design_svg' => $this->getFirstMediaUrl('designs'),
            'back_design_image' => $this->getFirstMediaUrl('back_designs'),
            'current_version' => $this->current_version,
            'product' => $this->when($designable, function () use ($designable) {
                return  new CategoryResource($designable instanceof Category ? $designable : $designable->category);
            }),
            'selected_category' => $this->when(
                $designable instanceof Product,
                fn() => CategoryResource::make($designable)
            ),

            'owner' => UserResource::make($this->whenLoaded('owner')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'template' => TemplateResource::make($this->whenLoaded('template')),
            'dimension' => DimensionResource::make($this->whenLoaded('dimension')),
            'productPrice' => ProductPriceResource::make($this->whenLoaded('productPrice')),
            'specs' => $this->whenLoaded('specifications', function () {
                return $this->specifications->map(function ($spec) {
                    return [
                        'id'     => $spec->id,
                        'name'   => $spec->name,
                        'option' => ProductSpecificationOption::make($spec->pivot->option),
                        'options' => ProductSpecificationOption::collection($spec->options),
                    ];
                });
            }),

            'placed_on' => optional($this->pivot)->created_at?->format('d/m/Y'),
            'price' => $this->total_price ?? $this->price,
            'quantity' => $this->productPrice?->quantity ?? $this->quantity,
            'ownered_by_me' => $this->owner?->id == auth('sanctum')->user()?->id,
            'type' => 'design',
            'delete_since' => $this->deleted_at?->diffForHumans(),
            'is_saved' => $this->when(!$this->deleted_at,fn() => $this->saves->contains(fn($save) => $save->user_id === auth('sanctum')->id()),),
            'orientation' =>  [
                'value' => $this->orientation?->value,
                'label' => $this->orientation?->label(),
            ],
            'design_price' => $this->price,
            'visible_download_btn' => (bool) $this->price,

//            'is_added_to_cart' => $this->isAddedToCart(),
        ];
    }
}
