<?php

namespace App\Http\Resources\Template;

use App\Http\Resources\DimensionResource;
use App\Http\Resources\FontResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\TagResource;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateResource extends JsonResource
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
            'name_en' => $this->getTranslation('name', 'en'),
            'name_ar' => $this->getTranslation('name', 'ar'),
            'description' => $this->description,
            'design_data' => $this->when(
                request()->boolean('with_design_data', true),
                $this->design_data
            ),
            'design_back_data' => $this->when(
                request()->boolean('with_design_data', true),
                $this->design_back_data
            ),
            'status' => $this->when(isset($this->status), [
                'value' => $this->status?->value,
                'label' => $this->status?->label(),
                'bgHex' => $this->status?->bgHex(),
                'textHex' => $this->status?->textHex(),
            ]),
            'types' => TypeResource::collection($this->whenLoaded('types')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'source_design_svg' => $this->when(isset($this->image), $this->image),
            'back_base64_preview_image' => $this->use_front_as_back
                ? $this->getFirstMediaUrl('templates-preview')
                : ($this->approach == 'without_editor'
                    ? $this->getFirstMediaUrl('back-templates-preview')
                    : $this->getFirstMediaUrl('back_templates')),
            'has_mockup' => (boolean)$this->products->contains('has_mockup', true),
            'last_saved' => $this->when(isset($this->updated_at), $this->updated_at?->format('d/m/Y, g:i A')),
            'template_model_image' => $this->getMedia('rendered_mockups')
                ->first(fn($m) =>
//                    in_array($m->getCustomProperty('side'), ['front', 'none','back']) &&
                    (int)$m->getCustomProperty('category_id') === (int)request('product_without_category_id') &&
                    (string)$m->getCustomProperty('template_id') === (string)$this->id
                )
                ?->getUrl() ?: $this->getFirstMediaUrl('template_model_image'),
            'orientation' => [
                'value' => $this->orientation?->value,
                'label' => $this->orientation?->label(),
            ],
            'dimension' => DimensionResource::make($this->whenLoaded('dimension')),
            'has_corner' => $this->has_corner,
            'has_safety_area' => $this->has_safety_area,
            'safety_area' => $this->safety_area,
            'border' =>(float) $this->border,
            'has_cut_margin' => (bool)$this->cut_margin,
            'cut_margin' => $this->cut_margin,
            'approach' => $this->approach,
            'colors' => $this->colors,
            'color_templates_media' => $this->when($this->approach == 'without_editor',function(){
               return MediaResource::collection( $this->getMedia('color_templates'));
            }),
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
            'price' => $this->price,
            'visible_download_btn' => $this->when($this->price,true),
        ];
    }


}
