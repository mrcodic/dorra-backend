<?php

namespace App\Http\Requests\AiCategory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAiCategoryQuestionsRequest extends FormRequest
{
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
                'exists:ai_guide_question_options,id',
            ],
        ];
    }
}
