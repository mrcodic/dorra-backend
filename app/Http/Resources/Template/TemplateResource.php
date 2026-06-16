<?php

namespace App\Http\Resources\Template;

use App\Enums\Template\TypeEnum;
use App\Http\Resources\DimensionResource;
use App\Http\Resources\FontResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\MockupResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\TableauSceneResource;
use App\Http\Resources\TagResource;
use App\Models\Category;
use App\Models\Guest;
use App\Models\Mockup;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class TemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $templateId = $this->id;

        $categoryId = (int)(request('product_without_category_id') ?? Product::find(request('product_id'))?->category_id);
        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::query()
            ->where('model_type', \App\Models\Mockup::class)
            ->where('collection_name', 'generated_mockups')
            ->where('custom_properties->template_id', (string)$this->id)
            ->where('custom_properties->model_image', 1)
            ->whereExists(function ($query) {
                $query->selectRaw(1)
                    ->from('mockups')
                    ->whereColumn('mockups.id', 'media.model_id')
                    ->whereNull('mockups.deleted_at')
                    ->when(
                        request('product_without_category_id'),
                        fn($q) => $q->where('mockups.category_id', request('product_without_category_id'))
                    );
            })
            ->where(function ($query) {
                $id = (int)(request('product_without_category_id') ?? request('product_id'));
                $query->where('custom_properties->category_id', $id)
                    ->orWhereJsonContains('custom_properties->product_ids', $id);
            })
            ->when(
                request('product_id'),
                fn($query) => $query->whereExists(function ($query) {
                    $query->selectRaw(1)
                        ->from('mockup_product')
                        ->whereColumn('mockup_product.mockup_id', 'media.model_id')
                        ->where('mockup_product.product_id', (int)request('product_id'));
                })
            )
            ->first();

        $categoryId = Product::find(request('product_id'))?->category?->id;

        $backPreviewImage = $this->use_front_as_back
            ? $this->getFirstMedia('templates-preview')
            : ($this->approach == 'without_editor'
                ? $this->getFirstMedia('back-templates-preview')
                : $this->getFirstMedia('back_templates'));
        $backPreviewImageUrl = $backPreviewImage?->getFullUrl();

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
            'mockups' => $this->whenLoaded('mockups', fn() => $this->mockups->map(function ($mockup) {
                $colors = $mockup->pivot->colors ?? [];
                $positions = is_array($mockup->pivot->positions)
                    ? $mockup->pivot->positions
                    : json_decode($mockup->pivot->positions ?? '[]', true);
                return [
                    'mockup_id' => $mockup->id,
                    'mockup_name' => $mockup->name,
                    'mockup_model_color' => $mockup->pivot->model_color,
                    'mockup_template_type' => $mockup->pivot->type,
                    'colors' => $colors,
                    'positions' => $positions,
                ];
            })->values()->all()
            ),
            'show_back' => (function () use ($media, $backPreviewImageUrl) {
                if (empty($this->image) && !empty($backPreviewImageUrl)) {
                    return true;
                }
                if (!empty($this->image) && !empty($backPreviewImageUrl)) {
                    if ($media) {
                        return $media->getCustomProperty('side') === 'back';
                    }
                    return true;
                }
                return false;
            })(),
            'source_design_svg' => $this->when(isset($this->image), $this->image),
            'back_base64_preview_image' => $backPreviewImageUrl,
            'template_image_height' => $this->image?->getCustomProperty('height') ?: $backPreviewImage?->getCustomProperty('height'),
            'template_image_width' => $this->image?->getCustomProperty('width') ?: $backPreviewImage?->getCustomProperty('height'),
            'has_mockup' => (boolean)$this->products->contains('has_mockup', true),
            'last_saved' => $this->when(isset($this->updated_at), $this->updated_at?->format('d/m/Y, g:i A')),
            'template_model_image' => $media
                ?->getUrl() ?: $this->getFirstMediaUrl('template_model_image'),
            'mockup_template_image' => (function () use ($categoryId) {
                $cartItemId = $this->additional['cart_item_id'] ?? null;
                $catId = $this->additional['category_id'] ?? $categoryId ?? null;

                if (!$cartItemId || !$catId) {
                    return null;
                }

                return \Spatie\MediaLibrary\MediaCollections\Models\Media::query()
                    ->where('model_type', \App\Models\Mockup::class)
                    ->where('collection_name', 'generated_mockups')
                    ->where('custom_properties->template_id', (string)$this->id)
                    ->where('custom_properties->cart_item_id', (string)$cartItemId)
                    ->where('custom_properties->category_id', (int)$catId)
                    ->whereExists(function ($query) {
                        $query->selectRaw(1)
                            ->from('mockups')
                            ->whereColumn('mockups.id', 'media.model_id')
                            ->whereNull('mockups.deleted_at');
                    })
                    ->first()
                    ?->getUrl();
            })(),
            'orientation' => [
                'value' => $this->orientation?->value,
                'label' => $this->orientation?->label(),
            ],
            'dimension' => DimensionResource::make($this->whenLoaded('dimension')),
            'has_corner' => $this->has_corner,
            'has_safety_area' => $this->has_safety_area,
            'safety_area' => $this->safety_area,
            'border' => (float)$this->border,
            'has_cut_margin' => (bool)$this->cut_margin,
            'cut_margin' => $this->cut_margin,
            'approach' => $this->approach,
            'colors' => $this->when(request()->has('mockup_id'), function () {
                $mockupId = request('mockup_id');
                if (!$mockupId) {
                    return [];
                }
                $mockup = $this->mockups()
                    ->where('mockups.id', $mockupId)
                    ->first();

                if (!$mockup || !$mockup->pivot) {
                    return [];
                }
                $colors = $mockup->pivot->colors ?? [];
                return is_array($colors)
                    ? $colors
                    : json_decode($colors ?: '[]', true);
            }),
            'template_colors' => $this->mockups()
                ->whereCategoryId($categoryId)
                ->whereNull('mockups.deleted_at')
                ->with('templates:id')
                ->get()
                ->sortByDesc(function ($mockup) use($templateId){
                    return $mockup->templates
                        ->first(fn($tpl) => $tpl->id == $templateId)
                        ?->pivot->model_color ? 1 : 0;
                })
                ->flatMap(function ($mockup) use ($templateId) {
                    return $mockup->templates
                        ->filter(fn($tpl) => $tpl->id == $templateId)
                        ->flatMap(function ($tpl) {
                            $colors = $tpl->pivot->colors ?? [];
                            if (is_string($colors)) {
                                $colors = json_decode($colors, true) ?: [];
                            }
                            $colors = is_array($colors) ? $colors : [];
                            $modelColor = $tpl->pivot->model_color ?? null;
                            if ($modelColor && in_array($modelColor, $colors)) {
                                $colors = array_merge(
                                    [$modelColor],
                                    array_values(array_filter($colors, fn($c) => $c !== $modelColor))
                                );
                            }

                            return $colors;
                        });
                })
                ->filter()
                ->unique()
                ->values(),
            'mockup_model_id' => $this->mockups()
                ->whereCategoryId($categoryId)
                ->wherePivot('model_color', '!=', null)
                ->whereRaw("mockup_template.model_color != ''")
                ->first()
                ?->id,
            'color_templates_media' => $this->when($this->approach == 'without_editor', function () {
                return MediaResource::collection($this->getMedia('color_templates'));
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

                            return $model->font->loadMissing(['fontStyles.media', 'fontStyles.font']);
                        })
                        ->filter()
                        ->unique('id')
                        ->values();
                })
            ),
            'price' => $this->price,
            'visible_download_btn' => $this->when($this->price, true),
            'attached_with_mockup' => $this->when(request()->has('mockup_id'), function () {
                $mockupId = request('mockup_id');
                if (!$mockupId) {
                    return false;
                }

                if ($this->relationLoaded('mockups')) {
                    $mockup = $this->mockups->firstWhere('id', (int)$mockupId);
                    if (!$mockup) return false;

                    $pivot = $mockup->pivot;
                    return
                        !empty($pivot->positions) ;
//                        &&$pivot->colors === $mockup->colors
                }

                $mockup = $this->mockups()
                    ->where('mockups.id', $mockupId)
                    ->first();

                if (!$mockup) return false;

                $pivot = $mockup->pivot;
                return
                    !empty($pivot->positions) ;
//                    &&$pivot->colors === $mockup->colors
            }),

            'mockup_template_type' => $this->when(request()->has('mockup_id'), function () {
                $mockupId = request('mockup_id');
                if (!$mockupId) {
                    return '';
                }

                $mockup = $this->mockups()
                    ->where('mockups.id', $mockupId)
                    ->first();

                if (!$mockup) return '';

                $pivot = $mockup->pivot;
                return $pivot->type;
            }),
            'tableau_scenes' => TableauSceneResource::collection( $this->whenLoaded('tableauScenes')),
        ];
    }
}
