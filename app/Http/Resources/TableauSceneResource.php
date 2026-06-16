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
            'image_url' => $this->imageUrl(),
        ];
    }
}
