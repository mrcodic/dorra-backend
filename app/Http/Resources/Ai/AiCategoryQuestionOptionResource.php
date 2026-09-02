<?php

namespace App\Http\Resources\Ai;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiCategoryQuestionOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label,
            'prompt_value' => $this->prompt_value,
        ];
    }
}
