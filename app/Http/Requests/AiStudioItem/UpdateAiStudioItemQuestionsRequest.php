<?php

namespace App\Http\Requests\AiStudioItem;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAiStudioItemQuestionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'questions' => [
                'nullable',
                'array',
            ],

            'questions.*.question_id' => [
                'required',
                'integer',
                'distinct',
                'exists:ai_guide_questions,id',
            ],

            'questions.*.selected' => [
                'nullable',
                'boolean',
            ],

            'questions.*.required' => [
                'nullable',
                'boolean',
            ],

            'questions.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'questions.*.options' => [
                'nullable',
                'array',
            ],

            'questions.*.options.*' => [
                'integer',
                'distinct',
                'exists:ai_guide_question_options,id',
            ],
        ];
    }
}
