<?php

namespace App\Http\Resources\Ai;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiStudioItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'generation_type' => $this->generation_type?->value ?? $this->generation_type,
            'default_resolution' => $this->default_resolution,
            'aspect_ratio' => $this->aspect_ratio,
            'credits_cost' => $this->credits_cost,
            'sort_order' => $this->sort_order,
            'image' => $this->getFirstMediaUrl('image') ?: null,
        ];
    }
}
