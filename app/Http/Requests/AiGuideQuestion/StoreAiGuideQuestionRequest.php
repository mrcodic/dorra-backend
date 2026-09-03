<?php

namespace App\Http\Requests\AiGuideQuestion;

use App\Enums\Ai\AiGuideQuestionTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiGuideQuestionRequest extends FormRequest
{
    public function rules($id = null): array
    {
        $withOptions = in_array(request('type'), [
            AiGuideQuestionTypeEnum::SINGLE_SELECT->value,
            AiGuideQuestionTypeEnum::MULTI_SELECT->value,
        ], true);

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
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],

            'options' => [
                Rule::requiredIf($withOptions),
                'nullable',
                'array',
            ],

            'options.*.id' => [
                'nullable',
                'integer',
                'exists:ai_guide_question_options,id',
            ],

            'options.*.label' => [
                Rule::requiredIf($withOptions),
                'nullable',
                'array',
            ],

            'options.*.label.en' => [
                Rule::requiredIf($withOptions),
                'nullable',
                'string',
                'max:255',
            ],

            'options.*.label.ar' => [
                Rule::requiredIf($withOptions),
                'nullable',
                'string',
                'max:255',
            ],

            'options.*.prompt_value' => ['nullable', 'array'],

            'options.*.prompt_value.en' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'options.*.prompt_value.ar' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'options.*.is_active' => [
                'nullable',
                'boolean',
            ],
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
            'options.*.label.en' => 'English option label',
            'options.*.label.ar' => 'Arabic option label',
        ];
    }
}
