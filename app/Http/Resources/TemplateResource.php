<?php

namespace App\Http\Resources;

use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateResource extends JsonResource
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
            'name' => $this->name,
            'design_data' => $this->design_data,
            'width' => $this->width ?? 200,
            'height' => $this->height ?? 100,
            'unit' => $this->unit ?? "mm",
            'dpi' => $this->dpi ?? 300,
            'status' => [
                'value' => $this->status?->value,
                'label' => $this->status?->label()
            ],
            'product' => ProductResource::make($this->whenLoaded('product')),
//            'preview_image' => $this->getFirstMediaUrl('templates'),
            'source_design_svg' => $this->image,
            'base64_preview_image' => $this->image,

        ];
    }
}
