<?php

namespace App\Http\Resources\Favourite;

use App\Models\Category;
use App\Models\Product;
use App\Models\Template;
use App\Services\TemplateModelImageResolver;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavouriteItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $template = $this->favouritable;
        $context = $this->contextable;

        $templateModelImage = $template instanceof Template && ($context instanceof Product || $context instanceof Category)
            ? app(TemplateModelImageResolver::class)->resolve($template, $context)
            : null;

        return [
            'id' => $this->id,
            'is_favourite' => true,
            'favouritable_type' => $this->favouritableTypeAlias(),
            'favouritable_id' => (string) $this->favouritable_id,
            'contextable_type' => $this->contextableTypeAlias(),
            'contextable_id' => (string) $this->contextable_id,
            'template' => $template instanceof Template ? [
                'id' => (string) $template->id,
                'name' => $template->name,
                'name_en' => $template->getTranslation('name', 'en'),
                'name_ar' => $template->getTranslation('name', 'ar'),
                'slug' => $template->slug,
                'price' => $template->price,
                'approach' => $template->approach,
                'image' => $template->image,
                'template_model_image' => $templateModelImage,
            ] : null,
            'context' => $context ? [
                'type' => $this->contextableTypeAlias(),
                'id' => (string) $context->getKey(),
                'name' => $context->name ?? null,
                'slug' => $context->slug ?? null,
                'category_id' => $context instanceof Product ? $context->category_id : null,
            ] : null,
            'created_at' => $this->created_at,
        ];
    }

    private function favouritableTypeAlias(): string
    {
        return match (true) {
            $this->favouritable instanceof Template => 'template',
            default => class_basename((string) $this->favouritable_type),
        };
    }

    private function contextableTypeAlias(): string
    {
        return match (true) {
            $this->contextable instanceof Product => 'product',
            $this->contextable instanceof Category => 'category',
            default => class_basename((string) $this->contextable_type),
        };
    }
}
