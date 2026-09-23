<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Mockup;
use App\Models\Product;
use App\Models\Template;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TemplateModelImageResolver
{
    public function resolve(Template $template, Model $context): string
    {
        $contextId = $context->getKey();

        $media = Media::query()
            ->where('model_type', Mockup::class)
            ->where('collection_name', 'generated_mockups')
            ->where('custom_properties->template_id', (string) $template->getKey())
            ->where('custom_properties->model_image', 1)
            ->whereExists(function ($query) use ($context) {
                $query->selectRaw(1)
                    ->from('mockups')
                    ->whereColumn('mockups.id', 'media.model_id')
                    ->whereNull('mockups.deleted_at')
                    ->when(
                        $context instanceof Category,
                        fn ($q) => $q->where('mockups.category_id', $context->getKey())
                    );
            })
            ->where(function ($query) use ($contextId) {
                $query->where('custom_properties->category_id', $contextId)
                    ->orWhereJsonContains('custom_properties->product_ids', $contextId);
            })
            ->when(
                $context instanceof Product,
                fn ($query) => $query->whereExists(function ($query) use ($context) {
                    $query->selectRaw(1)
                        ->from('mockup_product')
                        ->whereColumn('mockup_product.mockup_id', 'media.model_id')
                        ->where('mockup_product.product_id', $context->getKey());
                })
            )
            ->latest()
            ->first();

        return $media?->getUrl()
            ?: $template->getFirstMediaUrl('template_model_image');
    }
}
