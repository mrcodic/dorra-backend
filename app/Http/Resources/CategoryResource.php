<?php

namespace App\Http\Resources;

use App\Enums\Template\StatusEnum;
use App\Http\Resources\Product\ProductPriceResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductSpecificationResource;
use App\Http\Resources\Template\TemplateResource;
use App\Models\Industry;
use App\Models\Media;
use App\Models\Product;
use App\Models\Template;
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
        $lastOffer = $this?->lastOffer;
        $sub = (float)$this->getAttribute('base_price');
        $val = (float)($lastOffer?->getRawOriginal('value') ?? 0);
        $after = $lastOffer ? round($sub * (1 - ($val / 100)), 2) : null;
        $templatePreviewData = $this->resolveTemplatePreviewData($request);
        return [
            'id' => $this->when(isset($this->id), $this->id),
            'name' => $this->when(isset($this->name), $this->name),
            'description' => $this->description,
            'is_tableau' => $this->is_tableau,
            'has_custom_prices' => $this->has_custom_prices,
            'base_price' => $this->base_price,
            'price_after_offer' => is_null($after) ? null : sprintf('%.2f', round($after, 2)),
            'custom_prices' => ProductPriceResource::collection($this->whenLoaded('prices')),
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl("categories")),
            'tableau_image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl("tableau_image")),
            'main_image' => $this->whenLoaded('media', function () {
                return MediaResource::make($this->getFirstMedia('categories'));
            }),
            'mobile_banner' => $this->whenLoaded('media', function () {
                return $this->getFirstMediaUrl('mobile_banner');

            }),
            'website_banner' => $this->whenLoaded('media', function () {
                return $this->getFirstMediaUrl('website_banner');
            }),
            'all_product_images' => $this->whenLoaded('media', function () {
                return MediaResource::collection($this->getAllCategoryImages());
            }),

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
                                ->filter(fn($option) => in_array((int)$option->id, $tableauSizeOptionIds, true))
                                ->values()
                        );
                    }

                    return $specification;
                });

                return ProductSpecificationResource::collection($specifications);
            }),
            'dimensions' => DimensionResource::collection($this->whenLoaded('dimensions')),
            'product_model_image' => $this->getFirstMediaUrl('category_model_image'),
            'sub_categories' => CategoryResource::collection(
                $this->whenLoaded('landingSubCategories', function () {
                    return $this->landingSubCategories->load('subCategoryProducts');
                })
            ),
            'sub_category_products' => ProductResource::collection(
                $this->whenLoaded('subCategoryProducts')
            ),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'category_products' => ProductResource::collection($this->whenLoaded('landingProducts')),
            'offer' => OfferResource::make($this->whenLoaded('lastOffer')),

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

                $subs = $industries->filter(fn($i) => !is_null($i->parent_id) && $i->children->isNotEmpty());


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
                    ->filter(fn($i) => !is_null($i->parent_id))
                    ->values();

                return IndustryResource::collection($subs);
            }),
            'templates' => TemplateResource::collection($this->whenLoaded('templates')),
            'is_has_category' => $this->is_has_category,
            'show_add_cart_btn' => $this->show_add_cart_btn,
            'show_customize_design_btn' => $this->show_customize_design_btn,
            'type' => 'category',
            'rating' => $this->is_has_category ? $this->products_rating : $this->rating,
            'reviews_count' => $this->is_has_category ? $this->products_reviews_count : $this->reviews_count,

            'colors' => $this->colors,
            'has_mockup' => (boolean)$this->has_mockup,
            'has_orientation' => (boolean)$this->has_orientation,
            'source_design_svg' => $templatePreviewData['source_design_svg'],
            'back_base64_preview_image' => $templatePreviewData['back_base64_preview_image'],
            'template_model_image' => $templatePreviewData['template_model_image'],
        ];
    }

    private function resolveTemplatePreviewData(Request $request): array
    {
        $templateId = (string)$request->get('template_id');
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

        $categoryId = (int)(
        $request->get('product_without_category_id')
            ?: $this->id
            ?: Product::query()->find($request->get('product_id'))?->category_id
        );

        $productId = (int)$request->get('product_id');

        $media = Media::query()
            ->where('model_type', \App\Models\Mockup::class)
            ->where('collection_name', 'generated_mockups')
            ->where('custom_properties->template_id', (string)$template->id)
            ->where('custom_properties->model_image', 1)
            ->whereExists(function ($query) use ($categoryId) {
                $query->selectRaw(1)
                    ->from('mockups')
                    ->whereColumn('mockups.id', 'media.model_id')
                    ->whereNull('mockups.deleted_at')
                    ->when($categoryId, fn($q) => $q->where('mockups.category_id', $categoryId));
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
