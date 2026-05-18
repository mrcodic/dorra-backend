<?php

namespace App\Http\Resources\Cart;

use App\Enums\DiscountCode\TypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isDownload = $this->items->every(
            fn($item) => $item->type == \App\Enums\Item\TypeEnum::DOWNLOAD
        );

        $subAfter = round(
            $this->items->sum(function ($item) {
                $hasOffer = (float)optional($item->cartable?->lastOffer)->getRawOriginal('value') > 0;

                if ($hasOffer) {
                    return (float)$item->sub_total_after_offer;
                }

                return (float)$item->sub_total - (float)($item->discount_amount ?? 0);
            }),
            2
        );

        $cartLevelDiscountCode = $this->discountCode?->scope === \App\Enums\DiscountCode\ScopeEnum::GENERAL
            ? $this->discountCode
            : null;

        return [
            "id"                    => $this->id,
            "items"                 => CartItemResource::collection($this->whenLoaded('items')),
            "all_items_are_download" => $isDownload,
            'sub_total'             => $subAfter,
            'total'                 => round(
                getTotalPrice($cartLevelDiscountCode, $subAfter, $this->delivery_amount, $isDownload),
                2
            ),
            'tax' => [
                'ratio' => !$isDownload ? setting('tax') * 100 . '%' : '0%',  // <-- also fixed missing % concat
                'value' => !$isDownload ? getPriceAfterTax(setting('tax'), $subAfter) : 0,
            ],
            'delivery' => $this->delivery_amount,
            'discount' => [
                'id'    => $this->discountCode?->id,
                'code'  => $this->discountCode?->code,
                'ratio' => $this->price
                    ? (
                        ($this->discountCode?->type === TypeEnum::PERCENTAGE
                            ? (intval($this->discountCode?->value * 100) == $this->discountCode?->value * 100
                                ? intval($this->discountCode?->value * 100)
                                : number_format($this->discountCode?->value * 100, 2, '.', '')
                            )
                            : (intval(($this->discountCode?->value / $this->price) * 100) == ($this->discountCode?->value / $this->price) * 100
                                ? intval(($this->discountCode?->value / $this->price) * 100)
                                : number_format(($this->discountCode?->value / $this->price) * 100, 2, '.', '')
                            )
                        ) . '%'
                    )
                    : '0%',
                'value' => getDiscountAmount($this->discountCode, $this->price) ?? 0,
            ],
        ];
    }
}

