<?php

namespace App\Http\Requests\AiStudioItem;

use App\Enums\Ai\AiGenerationTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAiStudioItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules($id = null): array
    {
        $id ??= $this->route('ai_studio_item')
            ?? $this->route('id');

        return [
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['required', 'string', 'max:255'],

            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string', 'max:2000'],
            'description.ar' => ['nullable', 'string', 'max:2000'],

            'generation_type' => [
                'required',
                Rule::enum(AiGenerationTypeEnum::class),
            ],

            'default_resolution' => [
                'nullable',
                'string',
                'max:50',
            ],

            'aspect_ratio' => [
                'nullable',
                'string',
                'max:20',
            ],

            'credits_cost' => [
                'required',
                'integer',
                'min:0',
            ],

            'provider' => [
                'nullable',
                'string',
                'max:100',
            ],

            'model' => [
                'nullable',
                'string',
                'max:150',
            ],

            'settings' => [
                'nullable',
                'array',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            /*
             * If `questions` is omitted on Update, existing Studio Item
             * question mappings remain untouched.
             *
             * To clear all mappings explicitly, submit:
             * questions: []
             */
            'questions' => [
                'nullable',
                'array',
            ],

            'questions.*' => [
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

            'questions.*.conditions' => [
                'nullable',
                'array',
            ],

            'questions.*.conditions.*' => [
                'array',
            ],

            'questions.*.conditions.*.parent_question_id' => [
                'required',
                'integer',
                'exists:ai_guide_questions,id',
            ],

            'questions.*.conditions.*.parent_option_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'questions.*.conditions.*.parent_option_ids.*' => [
                'integer',
                'exists:ai_guide_question_options,id',
            ],

            'questions.*.conditions.*.operator' => [
                'nullable',
                Rule::in([
                    'selected',
                    'not_selected',
                ]),
            ],
        ];
    }
}
