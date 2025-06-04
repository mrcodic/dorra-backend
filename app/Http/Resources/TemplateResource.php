<?php

namespace App\Http\Resources;

use App\Enums\Template\UnitEnum;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductSpecificationResource;
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
            'width'  => $this->unit == UnitEnum::INCH ? $this->width * 25.4 : $this->width,
            'height' => $this->unit == UnitEnum::INCH ? $this->height * 25.4 : $this->height,

            'original_width' => $this->width,
            'original_height' => $this->height,
            'unit' => [
                'value' => $this->unit?->value,
                'label' => $this->unit?->label()
            ],
            'dpi' => $this->dpi ?? 300,
            'status' => [
                'value' => $this->status?->value,
                'label' => $this->status?->label()
            ],
            'type' => [
                'value' => $this->type?->value,
                'label' => $this->type?->label()
            ],
            'product' => ProductResource::make($this->whenLoaded('product')),
            'specs' => ProductSpecificationResource::collection($this->whenLoaded('specifications')),
            'source_design_svg' => $this->image,
//            'base64_preview_image' => $this->image,
            'last_saved' => $this->updated_at->format('d/m/Y, g:i A')

            ,

        ];
    }
}
