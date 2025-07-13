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
            'id' => $this->when(isset($this->id), $this->id),
            'name' => $this->when(isset($this->name), $this->name),
            'description' => $this->description,
            'design_data' =>  $this->design_data,
            'width' => $this->when(isset($this->width_pixel), $this->width_pixel),
            'height' => $this->when(isset($this->height_pixel), $this->height_pixel),
            'original_width' => $this->when(isset($this->width), $this->width),
            'original_height' => $this->when(isset($this->height), $this->height),
            'unit' => $this->when(isset($this->unit), [
                'value' => $this->unit?->value,
                'label' => $this->unit?->label(),
            ]),
            'dpi' => $this->when(isset($this->dpi), $this->dpi ?? 300),
            'status' => $this->when(isset($this->status), [
                'value' => $this->status?->value,
                'label' => $this->status?->label(),
            ]),
            'type' => $this->when(isset($this->type), [
                'value' => $this->type?->value,
                'label' => $this->type?->label(),
            ]),
            'product' => ProductResource::make($this->whenLoaded('product')),
            'specs' => ProductSpecificationResource::collection($this->whenLoaded('specifications')),
            'source_design_svg' => $this->when(isset($this->image), $this->image),
            'base64_preview_image' => $this->when(isset($this->image), $this->image),
            'product_type' => $this->product?->getTranslation('name','en') == 'T-shirt' ? 'T-shirt' : 'other' ,
            'last_saved' => $this->when(isset($this->updated_at), $this->updated_at?->format('d/m/Y, g:i A')),
        ];
    }

}
