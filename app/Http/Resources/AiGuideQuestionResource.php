<?php

namespace App\Http\Resources;

use App\Enums\Ai\AiGuideQuestionTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiGuideQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->key,
            'title' => $this->title,
            'type' => $this->type->value,
            'required' => $this->required,
            'placeholder' => $this->whenNotNull($this->placeholder),
            'promptLabel' => $this->prompt_label,
            'options' => $this->when(
                $this->type === AiGuideQuestionTypeEnum::SINGLE_SELECT,
                fn() => AiGuideQuestionOptionResource::collection($this->options)
            ),
        ];
    }
}
