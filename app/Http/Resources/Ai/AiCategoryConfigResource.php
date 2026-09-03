<?php

namespace App\Http\Resources\Ai;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiCategoryConfigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ],

            'generationType' => $this->generation_type->value,

            'defaultResolution' => $this->default_resolution,

            'aspectRatio' => $this->aspect_ratio,

            'creditsCost' => $this->credits_cost,

            'settings' => $this->settings ?? [],

            'questions' => AiCategoryQuestionResource::collection(
                $this->questions
            ),
        ];
    }
}
