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
        return [
            "id" => $this->id,
            "items" => CartItemResource::collection($this->whenLoaded('items')),
            'sub_total' => $this->price,
            'total' => getTotalPrice($this->discountCode ?? 0, $this->price),
            'tax' => [
                'ratio' => setting('tax') * 100 . "%",
                'value' => getPriceAfterTax(setting('tax'), $this->price),
            ],
            'delivery' => setting('delivery'),
            'discount' => [
                'code' => $this->discountCode?->code,
                'ratio' => $this->price
                    ? (
                        ($this->discountCode?->type === TypeEnum::PERCENTAGE
                            ? number_format( $this->discountCode?->value * 100, 2, '.', '')
                            : number_format( ($this->discountCode?->value / $this->price) * 100, 2, '.', '')
                        ) . '%'
                    )
                    : '0%',
                'value' =>getDiscountAmount($this->discountCode, $this->price)  ?? 0,
            ],

        ];
    }
}

