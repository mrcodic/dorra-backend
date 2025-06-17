<?php

namespace App\Http\Resources\Design;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DesignVersionResource extends JsonResource
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
            'design_image' => $this->getFirstMediaUrl('design-versions'),
            'version' => $this->version,

        ];
    }
}
