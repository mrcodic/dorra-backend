<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class TableauSceneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'top_position' => $this->top_position,
            'left_position' => $this->left_position,
            'positions' =>  $this->pivot?->positions ?? [],
            'image_url' => $this->imageUrl(),
            'scene_image_id' => $this->getFirstMedia('tableau_scene_image')?->id,
        ];
    }
}
