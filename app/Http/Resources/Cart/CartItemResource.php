<?php

namespace App\Http\Resources\Cart;

use App\Enums\DiscountCode\TypeEnum;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\Design\DesignResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Template\TemplateResource;
use App\Models\Design;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $item = $this->itemable;
        $cartable = $this->cartable;

        $isBundleItem = ! empty($this->bundle_group_key) || ! empty($this->bundle_id);

        $cartHasDiscount = $this->cart?->discount_amount > 0
            || $this->cart?->items()->where('discount_amount', '>', 0)->exists();

        $lastOffer = $cartHasDiscount ? null : $cartable?->lastOffer;

        $sub = (float) $this->getAttribute('sub_total');
        $val = (float) ($lastOffer?->getRawOriginal('value') ?? 0);
        $after = $lastOffer ? round($sub * (1 - ($val / 100)), 2) : null;

        $itemDiscountAmount = (float) ($this->discount_amount ?? 0);
        $priceAfterDiscount = max(0, $sub - $itemDiscountAmount);

        return [
            'id' => $this->id,

            'type' => $this->when($item, class_basename($item)),

            'item' => $this->when($item, function () use ($cartable, $item) {
                return $item instanceof Design
                    ? new DesignResource($item)
                    : (new TemplateResource($item))->additional([
                        'cart_item_id' => $this->id,
                        'category_id' => $cartable->category_id ?? $cartable->id,
                    ]);
            }),

            'show_edit_design' => $item instanceof Design,

            'specs' => CartItemSpecsResource::collection($this->whenLoaded('specs')),

            'product' => $this->when($cartable, function () use ($cartable) {
                return $cartable instanceof Product
                    ? new ProductResource($cartable->load('lastOffer'))
                    : new CategoryResource($cartable->load('lastOffer'));
            }),

            'price' => $this->sub_total && $cartable
                ? $this->sub_total
                : $item?->template?->price,

            'product_price' => $this->product_price,

            'price_after_offer' => $itemDiscountAmount > 0
                ? sprintf('%.2f', $priceAfterDiscount)
                : ($lastOffer ? sprintf('%.2f', $after) : null),

            'quantity' => $this->quantity,
            'color' => $this->color,

            'item_type' => [
                'value' => $this->type?->value,
                'label' => $this->type?->label(),
            ],

            'is_bundle_item' => $isBundleItem,
            'bundle_id' => $this->bundle_id,
            'bundle_item_id' => $this->bundle_item_id,
            'bundle_group_key' => $this->bundle_group_key,
            'bundle_role' => $this->bundle_role,

            'discount' => [
                'id' => $isBundleItem ? null : $this->discountCode?->id,
                'code' => $isBundleItem ? null : $this->discountCode?->code,
                'type' => $isBundleItem
                    ? 'bundle'
                    : ($this->discountCode ? 'discount_code' : null),
                'ratio' => $this->resolveDiscountRatio($isBundleItem, $itemDiscountAmount),
                'value' => $this->resolveDiscountValue($isBundleItem, $itemDiscountAmount),
            ],
        ];
    }

    private function resolveDiscountRatio(bool $isBundleItem, float $itemDiscountAmount): string
    {
        $subTotal = (float) ($this->sub_total ?? 0);

        if ($subTotal <= 0) {
            return '0%';
        }

        if ($isBundleItem || $itemDiscountAmount > 0) {
            return $this->formatPercentage(($itemDiscountAmount / $subTotal) * 100);
        }

        if (! $this->discountCode) {
            return '0%';
        }

        if ($this->discountCode->type === TypeEnum::PERCENTAGE) {
            return $this->formatPercentage((float) $this->discountCode->value * 100);
        }

        return $this->formatPercentage(((float) $this->discountCode->value / $subTotal) * 100);
    }

    private function resolveDiscountValue(bool $isBundleItem, float $itemDiscountAmount): float
    {
        if ($isBundleItem || $itemDiscountAmount > 0) {
            return round($itemDiscountAmount, 2);
        }

        return round((float) (getDiscountAmount($this->discountCode, $this->sub_total) ?? 0), 2);
    }

    private function formatPercentage(float $value): string
    {
        return rtrim(
                rtrim(number_format($value, 2, '.', ''), '0'),
                '.'
            ) . '%';
    }
}
