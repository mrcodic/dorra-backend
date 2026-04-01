<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FontResource extends JsonResource
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
            'has_regular_and_bold' => $this->fontStyles()
                    ->whereIn('name', ['regular', 'bold'])
                    ->distinct('name')
                    ->count() >= 2,
            'font_styles' => FontStyleResource::collection($this->whenLoaded('fontStyles')),
        ];
    }
}
