<?php

namespace App\Http\Requests\AiCategory;

use App\Enums\Ai\AiGenerationTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiCategoryRequest extends FormRequest
{
    public function rules($id = null): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
                Rule::unique('ai_categories', 'category_id')->ignore($id),
            ],
            'enabled' => ['required', 'boolean'],
            'default_resolution' => ['nullable', 'string', 'max:50'],
            'aspect_ratio' => ['nullable', 'string', 'max:30'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'settings' => ['nullable', 'array'],
            'settings.transparent_background' => ['nullable', 'boolean'],
            'settings.print_ready' => ['nullable', 'boolean'],
            'settings.orientation' => ['nullable', 'string', 'max:30'],
            'studio_items' => ['required', 'array', 'min:1'],
            'studio_items.*' => [
                'required',
                'integer',
                'distinct',
                'exists:ai_studio_items,id',
            ],

            'questions' => ['nullable', 'array'],
            'questions.*.question_id' => [
                'required',
                'integer',
                'distinct',
                'exists:ai_guide_questions,id',
            ],
            'questions.*.selected' => ['nullable', 'boolean'],
            'questions.*.required' => ['nullable', 'boolean'],
            'questions.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*' => [
                'integer',
                'distinct',
                'exists:ai_guide_question_options,id',
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
            'category_id' => 'product',
            'prompt_template_id' => 'prompt template',
            'generation_type' => 'generation type',
            'default_resolution' => 'default resolution',
            'credits_cost' => 'credits cost',
        ];
    }
}
