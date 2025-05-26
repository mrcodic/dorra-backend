<?php

namespace App\Http\Resources;

use App\Http\Resources\Product\ProductResource;
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
            'design_url' => $this->design_url,
            'current_version' => $this->current_version,
            'product' => ProductResource::make($this->whenLoaded('product')),
            'template' => TemplateResource::make($this->whenLoaded('template')),
        ];
    }
}
