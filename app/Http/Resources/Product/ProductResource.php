<?php

namespace App\Http\Resources\Product;

use App\Enums\Template\StatusEnum;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\DimensionResource;
use App\Http\Resources\IndustryResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\OfferResource;
use App\Http\Resources\TagResource;
use App\Http\Resources\Template\TemplateResource;
use App\Models\Media;
use App\Models\Product;
use App\Models\Template;
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
        $lastOffer = $this?->lastOffer;
        $sub  = (float) $this->getAttribute('base_price');
        $val  = (float) ($lastOffer?->getRawOriginal('value') ?? 0);
        $after = $lastOffer ? round($sub * (1 - ($val / 100)), 2) : null;
        $templatePreviewData = $this->resolveTemplatePreviewData($request);
        return [
            'id' => $this->when(isset($this->id), $this->id),
            'name' => $this->when(isset($this->name), $this->name),
            'description' => $this->when(isset($this->description), $this->description),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'has_custom_prices' => $this->when(isset($this->has_custom_prices), $this->has_custom_prices),
            'base_price' => $this->when(isset($this->base_price), $this->base_price),
            'price_after_offer' => is_null($after) ? null : sprintf('%.2f', round($after, 2)),

            'custom_prices' => ProductPriceResource::collection($this->whenLoaded('prices')),
            'rating' => $this->rating,
            'reviews_count' => $this->when(isset($this->reviews_count), $this->reviews_count),
            'type' => 'product',
            'main_image' => $this->whenLoaded('media', function () {
                return MediaResource::make($this->getFirstMedia('product_main_image'));
            }),
            'tableau_image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl("tableau_image")),

            'mobile_banner' => $this->whenLoaded('media', function () {
                return MediaResource::make($this->getFirstMedia('mobile_banner'));
            }),
            'website_banner' => $this->whenLoaded('media', function () {
                return MediaResource::make($this->getFirstMedia('website_banner'));
            }),
            'all_product_images' => $this->whenLoaded('media', function () {
                return MediaResource::collection($this->getAllProductImages());
            }),
            'templates' => TemplateResource::collection($this->whenLoaded('templates')),

            'template_tags' => $this->whenLoaded('templates', function () {
                $this->templates->loadMissing('tags');
                $tags = $this->templates
                    ->filter(fn($t) => $t->status === StatusEnum::LIVE)
                    ->pluck('tags')
                    ->flatten()
                    ->unique('id')
                    ->values();

                return TagResource::collection($tags);
            }),

            'template_industries' => $this->whenLoaded('templates', function () {

                $this->loadMissing('templates.industries.parent');

                $industries = $this->templates
                    ->filter(fn($t) => $t->status === StatusEnum::LIVE)
                    ->flatMap->industries
                    ->filter()
                    ->unique('id');

                $subs = $industries->filter(fn($i) => !is_null($i->parent_id)&& $i->children->isNotEmpty());


                $parents = $industries
                    ->filter(fn($i) => is_null($i->parent_id))
                    ->values();

                if ($subs->isNotEmpty()) {
                    $parents = $parents
                        ->merge($subs->pluck('parent')->filter())
                        ->unique('id')
                        ->values();
                }



                return IndustryResource::collection($parents);
            }),
            'template_sub_industries' => $this->whenLoaded('templates', function () {
                $this->loadMissing('templates.industries.parent');

                $industries = $this->templates
                    ->filter(fn($t) => $t->status === StatusEnum::LIVE)
                    ->flatMap->industries
                    ->filter()
                    ->unique('id');

                $subs = $industries
                    ->filter(fn ($i) => !is_null($i->parent_id))
                    ->values();

                return IndustryResource::collection($subs);
            }),

            'dimensions' => DimensionResource::collection($this->whenLoaded('dimensions')),
            'offer' => OfferResource::make($this->whenLoaded('lastOffer')),

        'specs' => $this->whenLoaded('specifications', function () use ($request) {
        $templateId = $request->get('template_id');
        $tableauSizeOptionIds = $this->resolveTemplateSpecOptionIds($templateId);

        $specifications = $this->specifications->map(function ($specification) use ($tableauSizeOptionIds) {
            if (
                $specification->fixed_key === 'tableau_size'
                && !empty($tableauSizeOptionIds)
                && $specification->relationLoaded('options')
            ) {
                $specification = clone $specification;
                $specification->setRelation(
                    'options',
                    $specification->options
                        ->filter(fn ($option) => in_array((int) $option->id, $tableauSizeOptionIds, true))
                        ->values()
                );
            }

            return $specification;
        });

        return ProductSpecificationResource::collection($specifications);
    }),
            'is_saved' => $this->when(
                $this->relationLoaded('saves') && is_null($this->deleted_at),
                fn() => $this->saves->contains(fn($save) => $save->user_id === auth('sanctum')->id())
            ),
            'has_mockup' => (boolean) $this->has_mockup,
            'product_model_image' => $this->getFirstMediaUrl('product_model_image'),
            'colors' => $this->colors,
            'show_add_cart_btn' => $this->show_add_cart_btn,
            'show_customize_design_btn' => $this->show_customize_design_btn,
            'source_design_svg' => $templatePreviewData['source_design_svg'],
            'back_base64_preview_image' => $templatePreviewData['back_base64_preview_image'],
            'template_model_image' => $templatePreviewData['template_model_image'],
            'is_tableau' => $this->category->is_tableau,
        ];
    }
    private function resolveTemplatePreviewData(Request $request): array
    {
        $templateId = (string) $request->get('template_id');

        if (!$templateId) {
            return [
                'source_design_svg' => null,
                'back_base64_preview_image' => null,
                'template_model_image' => null,
            ];
        }

        $template = Template::query()
            ->with('media')
            ->find($templateId);

        if (!$template) {
            return [
                'source_design_svg' => null,
                'back_base64_preview_image' => null,
                'template_model_image' => null,
            ];
        }

        $categoryId = (int) (
        $request->get('product_without_category_id')
            ?: $this->category_id
            ?: Product::query()->find($request->get('product_id'))?->category_id
        );

        $productId = (int) $request->get('product_id') ?? $this->id;

        $media = Media::query()
            ->where('model_type', \App\Models\Mockup::class)
            ->where('collection_name', 'generated_mockups')
            ->where('custom_properties->template_id', (string) $template->id)
            ->where('custom_properties->model_image', 1)
            ->whereExists(function ($query) use ($categoryId) {
                $query->selectRaw(1)
                    ->from('mockups')
                    ->whereColumn('mockups.id', 'media.model_id')
                    ->whereNull('mockups.deleted_at')
                    ->when($categoryId, fn ($q) => $q->where('mockups.category_id', $categoryId));
            })
            ->where(function ($query) use ($categoryId, $productId) {
                if ($categoryId) {
                    $query->where('custom_properties->category_id', $categoryId);
                }

                if ($productId) {
                    $query->orWhereJsonContains('custom_properties->product_ids', $productId);
                }
            })
            ->when($productId, function ($query) use ($productId) {
                $query->whereExists(function ($query) use ($productId) {
                    $query->selectRaw(1)
                        ->from('mockup_product')
                        ->whereColumn('mockup_product.mockup_id', 'media.model_id')
                        ->where('mockup_product.product_id', $productId);
                });
            })
            ->latest('id')
            ->first();

        $backPreviewImage = $template->use_front_as_back
            ? $template->getFirstMediaUrl('templates-preview')
            : (
            $template->approach === 'without_editor'
                ? $template->getFirstMediaUrl('back-templates-preview')
                : $template->getFirstMediaUrl('back_templates')
            );

        return [
            'source_design_svg' => $template->image,
            'back_base64_preview_image' => $backPreviewImage,
            'template_model_image' => $media?->getUrl() ?: $template->getFirstMediaUrl('template_model_image'),
        ];
    }

    private function resolveTemplateSpecOptionIds(?string $templateId): array
    {
        if (!$templateId) {
            return [];
        }

        static $cache = [];

        if (array_key_exists($templateId, $cache)) {
            return $cache[$templateId];
        }

        $ids = Template::query()
            ->with('specificationOptions:id')
            ->find($templateId)
            ?->specificationOptions
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all() ?? [];

        return $cache[$templateId] = $ids;
    }
}
