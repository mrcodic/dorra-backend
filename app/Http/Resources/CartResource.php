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
            "designs" => DesignResource::collection( $this->whenLoaded('designs')),
            'price' => $this->price,
            'tax' =>  setting('tax') * 100 ." %",
            'delivery' =>  setting('delivery'),
            'discount' => 0 ." %",
        ];
    }
}

