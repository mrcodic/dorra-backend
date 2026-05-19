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
        $cartHasDiscount = $this->cart?->discount_amount > 0
            || $this->cart?->items()->where('discount_amount', '>', 0)->exists();

        $lastOffer = $cartHasDiscount ? null : $cartable?->lastOffer;
        $sub   = (float) $this->getAttribute('sub_total');
        $val   = (float) ($lastOffer?->getRawOriginal('value') ?? 0);
        $after = $lastOffer ? round($sub * (1 - ($val / 100)), 2) : null;

        $priceAfterDiscountCode = max(0, $this->sub_total - $this->discount_amount);

        return [
            'id'   => $this->id,
            'type' => $this->when($item, class_basename($item)),
            'item' => $this->when($item, function () use ($cartable, $item) {
                return $item instanceof Design
                    ? new DesignResource($item)
                    : (new TemplateResource($item))->additional([
                        'cart_item_id' => $this->id,
                        'category_id'  => $cartable->category_id ?? $cartable->id,
                    ]);
            }),
            'show_edit_design' => $item instanceof Design,
            'specs'   => CartItemSpecsResource::collection($this->whenLoaded('specs')),
            'product' => $this->when($cartable, function () use ($cartable) {
                return $cartable instanceof Product
                    ? new ProductResource($cartable->load('lastOffer'))
                    : new CategoryResource($cartable->load('lastOffer'));
            }),
            'price'              => $this->sub_total && $cartable ? $this->sub_total : $item?->price,
            'product_price'      => $this->product_price,

            'price_after_offer'  => $this->discount_amount > 0
                ? sprintf('%.2f', $priceAfterDiscountCode)
                : ($lastOffer ? sprintf('%.2f', $after) : null),

            'quantity'  => $this->quantity,
            'color'     => $this->color,
            'item_type' => [
                'value' => $this->type?->value,
                'label' => $this->type?->label(),
            ],
            'discount' => [
                'id'    => $this->discountCode?->id,
                'code'  => $this->discountCode?->code,
                'ratio' => $this->sub_total > 0
                    ? (
                        ($this->discountCode?->type === TypeEnum::PERCENTAGE
                            ? (intval($this->discountCode?->value * 100) == $this->discountCode?->value * 100
                                ? intval($this->discountCode?->value * 100)
                                : number_format($this->discountCode?->value * 100, 2, '.', '')
                            )
                            : (intval(($this->discountCode?->value / $this->sub_total) * 100) == ($this->discountCode?->value / $this->sub_total) * 100
                                ? intval(($this->discountCode?->value / $this->sub_total) * 100)
                                : number_format(($this->discountCode?->value / $this->sub_total) * 100, 2, '.', '')
                            )
                        ) . '%'
                    )
                    : '0%',
                'value' => getDiscountAmount($this->discountCode, $this->sub_total) ?? 0,
            ],
        ];
    }
}
