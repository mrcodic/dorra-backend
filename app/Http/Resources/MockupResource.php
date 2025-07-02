<?php

namespace App\Http\Resources;

use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MockupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->when(isset($this->id), $this->id),
            'name' => $this->when(isset($this->name), $this->name),
            'type' => $this->when(isset($this->type), $this->type->label()),
            'colors' => $this->when(isset($this->colors), $this->colors),
            'product' => ProductResource::make($this->whenLoaded('product')),
            'mockup_url' => $this->getFirstMediaUrl('mockups'),
        ];
    }
}
