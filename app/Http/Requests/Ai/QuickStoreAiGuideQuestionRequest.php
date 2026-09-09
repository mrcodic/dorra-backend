<?php

namespace App\Http\Requests\Ai;

use App\Enums\Ai\AiGuideQuestionTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuickStoreAiGuideQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $options = collect($this->input('options', []))
            ->values()
            ->map(function ($option, $index) {
                $option = is_array($option) ? $option : [];

                $colors = collect(data_get($option, 'ui_data.colors', []))
                    ->filter(fn ($color) => is_string($color))
                    ->map(fn ($color) => strtoupper(trim($color)))
                    ->filter(fn ($color) => preg_match('/^#[0-9A-F]{6}$/', $color))
                    ->unique()
                    ->values()
                    ->all();

                if (empty($colors)) {
                    return $option;
                }

                $number = $index + 1;
                $prompt = 'Use this exact color palette: ' . implode(', ', $colors);

                data_set(
                    $option,
                    'label.en',
                    trim((string) data_get($option, 'label.en'))
                        ?: "Color Palette {$number}"
                );

                data_set(
                    $option,
                    'label.ar',
                    trim((string) data_get($option, 'label.ar'))
                        ?: "لوحة ألوان {$number}"
                );

                data_set(
                    $option,
                    'prompt_value.en',
                    trim((string) data_get($option, 'prompt_value.en'))
                        ?: $prompt
                );

                data_set(
                    $option,
                    'prompt_value.ar',
                    trim((string) data_get($option, 'prompt_value.ar'))
                        ?: $prompt
                );

                data_set(
                    $option,
                    'ui_data.colors',
                    $colors
                );

                return $option;
            })
            ->all();

        $hasPalette = collect($options)->contains(
            fn ($option) => !empty(data_get($option, 'ui_data.colors', []))
        );

        $this->merge([
            'type' => $hasPalette
                ? AiGuideQuestionTypeEnum::SINGLE_SELECT->value
                : $this->input('type'),
            'options' => $options,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'array'],
            'title.en' => ['required', 'string', 'max:255'],
            'title.ar' => ['nullable', 'string', 'max:255'],

            'type' => [
                'required',
                Rule::enum(AiGuideQuestionTypeEnum::class),
            ],

            'prompt_label' => ['nullable', 'array'],
            'prompt_label.en' => ['nullable', 'string', 'max:255'],
            'prompt_label.ar' => ['nullable', 'string', 'max:255'],

            'placeholder' => ['nullable', 'array'],
            'placeholder.en' => ['nullable', 'string', 'max:255'],
            'placeholder.ar' => ['nullable', 'string', 'max:255'],

            'required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],

            'options' => ['nullable', 'array'],

            'options.*.label' => ['required', 'array'],
            'options.*.label.en' => ['required', 'string', 'max:255'],
            'options.*.label.ar' => ['nullable', 'string', 'max:255'],

            'options.*.prompt_value' => ['nullable', 'array'],
            'options.*.prompt_value.en' => ['nullable', 'string'],
            'options.*.prompt_value.ar' => ['nullable', 'string'],

            'options.*.ui_data' => ['nullable', 'array'],
            'options.*.ui_data.colors' => ['nullable', 'array', 'max:10'],
            'options.*.ui_data.colors.*' => [
                'required',
                'string',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'options.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}
