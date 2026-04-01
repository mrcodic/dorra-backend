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
            'has_regular_and_bold' => (bool) $this->whenLoaded('fontStyles', fn() =>
                $this->fontStyles->contains(fn($style) => strtolower($style->name) === 'regular') &&
                $this->fontStyles->contains(fn($style) => strtolower($style->name) === 'bold')
            ),
            'font_styles' => FontStyleResource::collection($this->whenLoaded('fontStyles')),
        ];
    }
}
