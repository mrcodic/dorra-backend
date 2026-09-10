<?php

namespace App\Http\Requests\Ai;

use App\Enums\Ai\AiGenerationTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuickStoreAiStudioItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string', 'max:2000'],
            'description.ar' => ['nullable', 'string', 'max:2000'],
            'generation_type' => ['required', Rule::enum(AiGenerationTypeEnum::class)],
            'credits_cost' => ['required', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'settings' => ['nullable', 'array'],
            'settings.prompt_instructions' => ['nullable', 'string', 'max:5000'],
            'settings.negative_rules' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
