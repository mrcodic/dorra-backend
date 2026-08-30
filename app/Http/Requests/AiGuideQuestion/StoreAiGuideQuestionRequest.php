<?php

namespace App\Http\Requests\AiGuideQuestion;

use App\Enums\Ai\AiGuideQuestionTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiGuideQuestionRequest extends FormRequest
{
    public function rules($id = null): array
    {
        $singleSelect = request('type') === AiGuideQuestionTypeEnum::SINGLE_SELECT->value;

        return [
            'title' => ['required', 'array'],
            'title.en' => ['required', 'string', 'max:255'],
            'title.ar' => ['required', 'string', 'max:255'],

            'type' => ['required', Rule::enum(AiGuideQuestionTypeEnum::class)],

            'prompt_label' => ['required', 'array'],
            'prompt_label.en' => ['required', 'string', 'max:255'],
            'prompt_label.ar' => ['required', 'string', 'max:255'],

            'placeholder' => ['nullable', 'array'],
            'placeholder.en' => ['nullable', 'string', 'max:255'],
            'placeholder.ar' => ['nullable', 'string', 'max:255'],

            'required' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],

            'options' => array_filter([
                Rule::requiredIf($singleSelect),
                'nullable',
                'array',
                $singleSelect ? 'min:1' : null,
            ]),

            'options.*.id' => ['nullable', 'integer', 'exists:ai_guide_question_options,id'],

            'options.*.label' => [
                Rule::requiredIf($singleSelect),
                'nullable',
                'array',
            ],
            'options.*.label.en' => [
                Rule::requiredIf($singleSelect),
                'nullable',
                'string',
                'max:255',
            ],
            'options.*.label.ar' => [
                Rule::requiredIf($singleSelect),
                'nullable',
                'string',
                'max:255',
            ],

            'options.*.prompt_value' => ['nullable', 'array'],
            'options.*.prompt_value.en' => ['nullable', 'string', 'max:1000'],
            'options.*.prompt_value.ar' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [];
    }

    public function attributes(): array
    {
        return [
            'title.en' => 'English question',
            'title.ar' => 'Arabic question',
            'prompt_label.en' => 'English prompt label',
            'prompt_label.ar' => 'Arabic prompt label',
        ];
    }
}
