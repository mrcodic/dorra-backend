<?php

namespace App\Http\Resources\Cart;

use App\Enums\DiscountCode\TypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isDownload = $this->items->every(
            fn($item) => $item->type == \App\Enums\Item\TypeEnum::DOWNLOAD
        );

        $subAfter = round(
            $this->items->sum(function ($item) {
                $cartable = $item->cartable;

                $cartHasDiscount = $this->discount_amount > 0
                    || $this->items->contains(fn($i) => $i->discount_amount > 0);

                $lastOffer = $cartHasDiscount ? null : $cartable?->lastOffer;

                $sub = (float) $item->sub_total;
                $val = (float) ($lastOffer?->getRawOriginal('value') ?? 0);

                // Mirror CartItemResource: discount_amount takes priority over offer
                if ($item->discount_amount > 0) {
                    return max(0, $sub - (float) $item->discount_amount);
                }

                if ($lastOffer) {
                    return round($sub * (1 - ($val / 100)), 2);
                }

                return $sub;
            }),
            2
        );

        $cartLevelDiscountCode = $this->discountCode?->scope === \App\Enums\DiscountCode\ScopeEnum::GENERAL
            ? $this->discountCode
            : null;

        return [
            'id'                     => $this->id,
            'items'                  => CartItemResource::collection($this->whenLoaded('items')),
            'all_items_are_download' => $isDownload,
            'sub_total'              => $subAfter,
            'total'                  => round(
                getTotalPrice($cartLevelDiscountCode, $subAfter, $this->delivery_amount, $isDownload),
                2
            ),
            'tax' => [
                'ratio' => !$isDownload ? setting('tax') * 100 . '%' : '0%',
                'value' => !$isDownload ? getPriceAfterTax(setting('tax'), $subAfter) : 0,
            ],
            'delivery' => $this->delivery_amount,
            'discount' => [
                'id'    => $this->discountCode?->id,
                'code'  => $this->discountCode?->code,
                'ratio' => $subAfter > 0
                    ? (
                        ($this->discountCode?->type === TypeEnum::PERCENTAGE
                            ? (intval($this->discountCode?->value * 100) == $this->discountCode?->value * 100
                                ? intval($this->discountCode?->value * 100)
                                : number_format($this->discountCode?->value * 100, 2, '.', '')
                            )
                            : (intval(($this->discountCode?->value / $subAfter) * 100) == ($this->discountCode?->value / $subAfter) * 100
                                ? intval(($this->discountCode?->value / $subAfter) * 100)
                                : number_format(($this->discountCode?->value / $subAfter) * 100, 2, '.', '')
                            )
                        ) . '%'
                    )
                    : '0%',
                'value' => getDiscountAmount($this->discountCode, $subAfter) ?? 0,
            ],
        ];
    }
}
