<?php

namespace App\Http\Resources\Ai;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->category?->name,
            'generation_type' => $this->generation_type?->value ?? $this->generation_type,
            'default_resolution' => $this->default_resolution,
            'aspect_ratio' => $this->aspect_ratio,
            'credits_cost' => $this->credits_cost,
            'sort_order' => $this->sort_order,
            'image' => $this->category?->getFirstMediaUrl('categories') ?: null,
        ];
    }
}
