<?php

namespace App\Http\Requests\AiGuideQuestion;

use App\Enums\Ai\AiGuideQuestionTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAiGuideQuestionRequest extends FormRequest
{
    public function rules($id = null): array
    {
        $singleSelect = request('type') === AiGuideQuestionTypeEnum::SINGLE_SELECT->value;

        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(AiGuideQuestionTypeEnum::class)],
            'prompt_label' => ['required', 'string', 'max:255'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'required' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'options' => array_filter([
                Rule::requiredIf($singleSelect),
                'nullable',
                'array',
                $singleSelect ? 'min:1' : null,
            ]),
            'options.*.label' => ['required_if:type,' . AiGuideQuestionTypeEnum::SINGLE_SELECT->value, 'nullable', 'string', 'max:255'],
            'options.*.prompt_value' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
