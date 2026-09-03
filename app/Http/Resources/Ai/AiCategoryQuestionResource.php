<?php

namespace App\Http\Resources\Ai;

use App\Enums\Ai\AiGuideQuestionTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiCategoryQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $assignedOptionIds = collect(
            $this->assigned_option_ids ?? []
        )->map(fn($id) => (int) $id);

        $supportsOptions = in_array(
            $this->type,
            [
                AiGuideQuestionTypeEnum::SINGLE_SELECT,
                AiGuideQuestionTypeEnum::MULTI_SELECT,
            ],
            true
        );

        return [
            'id' => $this->key,

            'title' => $this->title,

            'type' => $this->type->value,

            'required' => (bool) $this->pivot->required,

            'placeholder' => $this->placeholder,

            'prompt_label' => $this->prompt_label,

            'options' => $supportsOptions
                ? AiCategoryQuestionOptionResource::collection(
                    $this->options->whereIn(
                        'id',
                        $assignedOptionIds
                    )
                )->resolve()
                : [],
        ];
    }
}
