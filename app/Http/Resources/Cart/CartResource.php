<?php

namespace App\Http\Resources\Cart;

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
            "items" =>  CartItemResource::collection($this->whenLoaded('items')) ,
            'sub_total' => $this->price,
            'total' => getTotalPrice(0,  $this->price),
            'tax' => [
                'ratio' => setting('tax') * 100 . "%",
                'value' => getPriceAfterTax(setting('tax'), $this->price),
            ],
            'delivery' => setting('delivery'),
            'discount' => [
                'ratio' => 0 . "%",
                'value' => 0,
            ],
        ];
    }
}

