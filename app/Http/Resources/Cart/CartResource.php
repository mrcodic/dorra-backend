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
        $subAfter = round(
            $this->items->sum(fn($item) => $item->sub_total_after_offer ?? $item->sub_total),
            2
        );
        $isDownload = $this->items->every(fn($item) => $item->type == \App\Enums\Item\TypeEnum::DOWNLOAD);
        return [
            "id" => $this->id,
            "items" => CartItemResource::collection($this->whenLoaded('items')),
            "all_items_are_download" => $isDownload,
            'sub_total' => $subAfter,
            'total' => round(getTotalPrice($this->discountCode ?? 0, $subAfter, $this->delivery_amount, $isDownload), 2),
            'tax' => [
                'ratio' => !$isDownload ? setting('tax') * 100 : 0 . "%",
                'value' => !$isDownload ? getPriceAfterTax(setting('tax'), $subAfter) : 0,
            ],
            'delivery' => $this->delivery_amount,
            'discount' => [
                'code' => $this->discountCode?->code,
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

