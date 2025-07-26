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
            'total' => getTotalPrice(0, $this->price),
            'tax' => [
                'ratio' => setting('tax') * 100 . "%",
                'value' => getPriceAfterTax(setting('tax'), $this->price),
            ],
            'delivery' => setting('delivery'),
            'discount' => [
                'ratio' => $this->discountCode?->type == TypeEnum::PERCENTAGE
                    ? $this->discountCode?->value
                    : ($this->discountCode?->value / $this->price) * 100 ?? 0 . "%",
                'value' => $this->discountCode?->value ?? 0,
            ],
        ];
    }
}

