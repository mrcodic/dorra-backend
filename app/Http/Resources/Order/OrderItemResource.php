<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Product\ProductSpecificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
           'product_name' => $this->design?->product->name,
           'quantity' => $this->design?->quantity,
            'total_price' => $this->design?->total_price,
            'design_image' => $this->design?->getFirstMediaUrl('designs'),
            'specs' => ProductSpecificationResource::collection($this->whenLoaded('design.specifications')),

        ];
    }
}
