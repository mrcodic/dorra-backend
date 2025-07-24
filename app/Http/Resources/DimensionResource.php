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
            'width' => $this->when(isset($this->width_cm), $this->width_cm),
            'height' => $this->when(isset($this->height_cm), $this->height_cm),
            'dpi' => 300,
            'unit' => $this->when(isset($this->unit), [
                'value' => $this->unit?->value,
                'label' => $this->unit?->label(),
            ]),
        ];
    }
}
