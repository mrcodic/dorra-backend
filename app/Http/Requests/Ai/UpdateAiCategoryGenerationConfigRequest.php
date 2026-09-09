<?php

namespace App\Http\Requests\Dashboard\Ai;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAiCategoryGenerationConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'studio_items' => ['required', 'array', 'min:1'],
            'studio_items.*' => ['required', 'integer', 'distinct', 'exists:ai_studio_items,id'],

            'questions' => ['nullable', 'array'],
            'questions.*.question_id' => ['required', 'integer', 'distinct', 'exists:ai_guide_questions,id'],
            'questions.*.selected' => ['nullable', 'boolean'],
            'questions.*.required' => ['nullable', 'boolean'],
            'questions.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*' => ['integer', 'distinct', 'exists:ai_guide_question_options,id'],
        ];
    }
}
