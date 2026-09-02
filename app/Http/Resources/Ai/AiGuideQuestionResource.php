<?php

namespace App\Http\Resources\Ai;

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
                in_array($this->type , [AiGuideQuestionTypeEnum::SINGLE_SELECT, AiGuideQuestionTypeEnum::MULTI_SELECT]),
                fn() => AiGuideQuestionOptionResource::collection($this->options)
            ),
        ];
    }
}
