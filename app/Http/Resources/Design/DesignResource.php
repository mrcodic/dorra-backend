<?php

namespace App\Http\Resources\Design;

use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\TemplateResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DesignResource extends JsonResource
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
            'design_data' => $this->design_data,
            'design_image' => $this->getFirstMediaUrl('designs'),
            'current_version' => $this->current_version,
            'product' => ProductResource::make($this->whenLoaded('product')),
            'template' => TemplateResource::make($this->whenLoaded('template')),
            'placed_on' => optional($this->pivot)->created_at->format('d/m/Y'),
            'price' => $this->total_price,
            'quantity' => $this->productPrice?->quantity ?? $this->quantity,
        ];
    }
}
