<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Product\ProductSpecificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

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
           'product_name' => $this->product?->name,
           'quantity' => $this->quantity,
            'color' => $this->color,
            'total_price' => $this->total_price,
            'design_image' => $this->itemable?->getFirstMediaUrl(Str::plural(Str::lower(class_basename($this->itemable)))),
            'specs' => OrderItemSpecResource::collection($this->whenLoaded('specs')),
        ];
    }
}
