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
            'font_styles' => $this->when(
                $this->relationLoaded('fontStyles'),
                FontStyleResource::collection(
                    $this->fontStyles()->paginate(request('styles_per_page', 5))
                )
            ),
        ];
    }
}
