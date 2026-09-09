<?php

namespace App\Models;

use App\Enums\Bundle\DiscountTypeEnum;
use App\Enums\Bundle\ItemRoleEnum;
use App\Enums\Bundle\QuantityRuleEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BundleItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'bundle_id',
        'itemable_type',
        'itemable_id',
        'role',
        'quantity_rule',
        'quantity',
        'price_id',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'role' => ItemRoleEnum::class,
            'quantity_rule' => QuantityRuleEnum::class,
            'discount_type' => DiscountTypeEnum::class,
            'quantity' => 'integer',
            'price_id' => 'integer',
            'discount_value' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }
    public function getTemplatePreviewData(?string $templateId = null): array
    {
        $templateId = (string) $templateId;

        if ($templateId === '') {
            return $this->emptyTemplatePreviewData();
        }

        $template = Template::query()
            ->with('media')
            ->find($templateId);

        if (! $template) {
            return $this->emptyTemplatePreviewData();
        }

        [$categoryId, $productId] = $this->resolveTemplatePreviewItemIds();

        if (! $categoryId && ! $productId) {
            return $this->emptyTemplatePreviewData();
        }

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
                    if ($categoryId) {
                        $query->orWhereJsonContains('custom_properties->product_ids', $productId);
                    } else {
                        $query->whereJsonContains('custom_properties->product_ids', $productId);
                    }
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

    private function resolveTemplatePreviewItemIds(): array
    {
        $item = $this->itemable;

        if ($item instanceof Product) {
            return [
                (int) $item->category_id,
                (int) $item->id,
            ];
        }

        if ($item instanceof Category) {
            return [
                (int) $item->id,
                0,
            ];
        }

        return [0, 0];
    }

    private function emptyTemplatePreviewData(): array
    {
        return [
            'source_design_svg' => null,
            'back_base64_preview_image' => null,
            'template_model_image' => null,
        ];
    }
}
