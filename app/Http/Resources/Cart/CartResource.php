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
        $subAfterOffer = round(
            $this->items->sum(fn($item) => $item->sub_total_after_offer ?? $item->sub_total),
            2
        );

        // Total discount from item-level codes (PRODUCT/CATEGORY scope)
        $itemsDiscount = $this->items->sum(function ($item) {
            // Items with offer → no discount code applied
            return $item->sub_total_after_offer ? 0 : ($item->discount_amount ?? 0);
        });

        // Final sub_total after offer + item discount codes
        $subAfterDiscount = round(max(0, $subAfterOffer - $itemsDiscount), 2);

        $isDownload = $this->items->every(fn($item) => $item->type == \App\Enums\Item\TypeEnum::DOWNLOAD);


        $totalDiscount = $subAfterDiscount;

        $subTotal = round($subAfterOffer - $totalDiscount, 2);

        return [
            'id'                    => $this->id,
            'items'                 => CartItemResource::collection($this->whenLoaded('items')),
            'all_items_are_download'=> $isDownload,
            'sub_total'             => $subTotal,
            'total'                 => round(getTotalPrice($this->discountCode ?? 0, $subTotal, $this->delivery_amount, $isDownload), 2),
            'tax'                   => [
                'ratio' => !$isDownload ? setting('tax') * 100 . '%' : '0%',
                'value' => !$isDownload ? getPriceAfterTax(setting('tax'), $subTotal) : 0,
            ],
            'delivery'              => $this->delivery_amount,
            'discount'              => [
                'id'    => $this->discountCode?->id,
                'code'  => $this->discountCode?->code,
                'ratio' => $subAfterOffer > 0
                    ? (function () use ($totalDiscount, $subAfterOffer) {
                        $ratio = ($totalDiscount / $subAfterOffer) * 100;
                        return (intval($ratio) == $ratio
                                ? intval($ratio)
                                : number_format($ratio, 2, '.', '')
                            ) . '%';
                    })()
                    : '0%',
                'value' => round($totalDiscount, 2),
            ],
        ];
    }
}

