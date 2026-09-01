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
            'prompt_template_id' => ['nullable', 'integer', 'exists:ai_prompt_templates,id'],
            'enabled' => ['required', 'boolean'],
            'generation_type' => ['required', Rule::enum(AiGenerationTypeEnum::class)],
            'default_resolution' => ['nullable', 'string', 'max:50'],
            'aspect_ratio' => ['nullable', 'string', 'max:30'],
            'credits_cost' => ['required', 'integer', 'min:0'],
            'provider' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:150'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'settings' => ['nullable', 'array'],
            'settings.transparent_background' => ['nullable', 'boolean'],
            'settings.print_ready' => ['nullable', 'boolean'],
            'settings.orientation' => ['nullable', 'string', 'max:30'],
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
