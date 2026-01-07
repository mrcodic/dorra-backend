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
            $this->items->sum(fn ($item) => $item->sub_total_after_offer ?? $item->sub_total),
            2
        );
        return [
            "id" => $this->id,
            "items" => CartItemResource::collection($this->whenLoaded('items')),
            "all_items_are_download" => $this->items->every(fn($item) => $item->type == \App\Enums\Item\TypeEnum::DOWNLOAD),
            'sub_total' => $subAfter,
            'total' =>round(getTotalPrice($this->discountCode ?? 0, $subAfter, $this->delivery_amount),2),
            'tax' => [
                'ratio' => setting('tax') * 100 . "%",
                'value' => getPriceAfterTax(setting('tax'), $subAfter),
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
                'value' =>getDiscountAmount($this->discountCode, $this->price)  ?? 0,
            ],

        ];
    }
}

