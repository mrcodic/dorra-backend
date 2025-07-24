<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DimensionResource extends JsonResource
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
            'width' => $this->when(isset($this->width_pixel), $this->width_pixel),
            'height' => $this->when(isset($this->height_pixel), $this->height_pixel),
            'dpi' => 300,
            'unit' => $this->when(isset($this->unit), [
                'value' => $this->unit?->value,
                'label' => $this->unit?->label(),
            ]),
        ];
    }
}
