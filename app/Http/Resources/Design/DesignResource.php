<?php

namespace App\Http\Resources\Design;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\DimensionResource;
use App\Http\Resources\FontResource;
use App\Http\Resources\MockupResource;
use App\Http\Resources\Product\ProductPriceResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductSpecificationOption;
use App\Http\Resources\Product\ProductSpecificationResource;
use App\Http\Resources\Template\TemplateResource;
use App\Http\Resources\Template\TypeResource;
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
            'types' => TypeResource::collection($this->whenLoaded('types')),
            'mockup_design_image' =>
                $this->getFirstMediaUrl('front-mockup-designs') ?:
                $this->getFirstMediaUrl('none-mockup-designs') ?:
                    $this->getFirstMediaUrl('back-mockup-designs')
            ,
            'current_version' => $this->current_version,
            'product' => $this->when($designable, function () use ($designable) {
                return  new CategoryResource($designable instanceof Category ? $designable : $designable->category);
            }),
            'designable'=> $this->when($designable, function () use ($designable) {
                return  $designable instanceof Category ? CategoryResource::make($designable) : ProductResource::make($designable);
            }),
            'selected_category' => $this->when(
                $designable instanceof Product,
                fn() => CategoryResource::make($designable)
            ),

            'owner' => UserResource::make($this->whenLoaded('owner')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'template' => TemplateResource::make($this->whenLoaded('template')),
            'mockup' => MockupResource::make($this->whenLoaded('mockup')),
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
            'price' => $this->total_price,
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
            'linked_to_mockup' =>  $this->linked_to_mockup,
            'mockup_color' =>  $this->mockup_color,
            'approach' =>  $this->approach,
            'design_mockup_area' =>  $this->design_mockup_area ,
            'font_media' => FontResource::collection(
                $this->whenLoaded('libraryMedia', function () {
                    return $this->libraryMedia
                        ->where('pivot.type', 'font')
                        ->sortByDesc('pivot.created_at')
                        ->values()
                        ->map(function ($media) {
                            $model = $media->model;
                            if (!$model) return null;

                            if (!$model instanceof \App\Models\FontStyle) return null;

                            $model->loadMissing('font');
                            if (!$model->font) return null;

                            return $model->font->loadMissing(['fontStyles.media','fontStyles.font']);
                        })
                        ->filter()
                        ->unique('id')
                        ->values();
                })
            ),
            'is_added_to_cart' => $this->isAddedToCart(),
        ];
    }
}
