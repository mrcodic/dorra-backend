<?php

namespace App\Http\Resources\Ai;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiGuideQuestionOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label,
            'promptValue' => $this->whenNotNull($this->prompt_value),
        ];
    }
}
