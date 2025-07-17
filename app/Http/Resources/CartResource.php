<?php

namespace App\Http\Resources;

use App\Http\Resources\Design\DesignResource;
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
            "designs" => DesignResource::collection($this->whenLoaded('designs')),
            'sub_total' => $this->designs->pluck('total_price')->sum(),
            'total' =>  $this->price,
            'tax' => [
                'ratio' => setting('tax') * 100 . "%",
                'value' => getPriceAfterTax(setting('tax'), $this->designs->pluck('total_price')->sum()),
            ],
            'delivery' => setting('delivery'),
            'discount' => [
                'ratio' => 0 . "%",
                'value' => 0,
            ],
        ];
    }
}

