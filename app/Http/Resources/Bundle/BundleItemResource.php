<?php

namespace App\Http\Resources\Bundle;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleItemResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        $item = $this->itemable;
        $isProduct = $item instanceof Product;
        $templatePreviewData = $this->resource->getTemplatePreviewData(
            $request->get('template_id')
        );
        return [
            'bundle_item_id' => $this->id,

            'role' => $this->role?->value ?? $this->role,

            'scope' => $isProduct
                ? 'with_category'
                : 'without_category',

            'parent_category_id' => $isProduct
                ? $item?->category_id
                : null,

            'item_id' => $item?->id,
            'item_type' => $item?->getMorphClass() == Product::class ? 'product' : 'category',
            'item_name' => $item?->name,

            'quantity_rule' => $this->quantity_rule?->value ?? $this->quantity_rule,
            'quantity' => $this->quantity,

            'price_id' => $this->price_id,
            'price_label' => $this->getPriceLabel(),

            'discount_type' => $this->discount_type?->value ?? $this->discount_type,
            'discount_value' => $this->discount_value,
            'max_discount_amount' => $this->max_discount_amount,

            'has_specs' => $this->hasSpecs($item),
            'has_custom_prices' => $this->hasCustomPrices($item),

            'require_customize_design' => $this->requiresCustomizeDesign($item),
            'source_design_svg' => $templatePreviewData['source_design_svg'],
            'back_base64_preview_image' => $templatePreviewData['back_base64_preview_image'],
            'template_model_image' => $templatePreviewData['template_model_image'],
        ];
    }

    private function hasSpecs($item): bool
    {
        if (! $item || ! method_exists($item, 'specifications')) {
            return false;
        }

        if ($item->relationLoaded('specifications')) {
            return $item->specifications->isNotEmpty();
        }

        return $item->specifications()->exists();
    }

    private function hasCustomPrices($item): bool
    {
        if (!$item) {
            return false;
        }

        return (bool) data_get($item, 'has_custom_prices', false);
    }

    private function requiresCustomizeDesign($item): bool
    {
        $canCustomize = (bool) data_get($item, 'show_customize_design_btn', false);
        $canAddToCart = (bool) data_get($item, 'show_add_cart_btn', false);

        return $canCustomize && ! $canAddToCart;
    }

    private function getPriceLabel(): ?string
    {
        if (! $this->price_id || ! $this->itemable) {
            return null;
        }

        if (! method_exists($this->itemable, 'prices')) {
            return null;
        }

        $price = $this->itemable
            ->prices()
            ->whereKey($this->price_id)
            ->first();

        if (! $price) {
            return null;
        }

        return $this->formatPriceOptionLabel($price);
    }

    private function getPriceQuantity($price): mixed
    {
        return data_get($price, 'quantity');
    }

    private function getPriceAmount($price): mixed
    {
        return data_get($price, 'price');
    }

    private function formatPriceOptionLabel($price): string
    {
        $quantity = $this->getPriceQuantity($price);
        $amount = $this->getPriceAmount($price);

        $label = $quantity
            ? $quantity . ' pcs'
            : 'Price Option #' . $price->id;

        if ($amount !== null) {
            $label .= ' - ' . $amount;
        }

        return $label;
    }
}
