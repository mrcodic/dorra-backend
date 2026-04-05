<?php

namespace App\Http\Requests\Template;

use App\Enums\OrientationEnum;
use App\Enums\SafetyAreaEnum;
use App\Enums\Template\TypeEnum;
use App\Http\Requests\Base\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateTemplateEditorRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        $template = $this->route('template');
        $types = $template?->types->pluck('value')->map(fn ($t) => $t->value)->toArray() ?? [];

        $hasFront = in_array(TypeEnum::FRONT->value, $types);
        $hasBack  = in_array(TypeEnum::BACK->value, $types);
        $hasNone  = in_array(TypeEnum::NONE->value, $types);

        $isUpdatingFrontDesign = $this->has('design_data');
        $isUpdatingBackDesign  = $this->has('design_back_data');

        $designDataRules = ($hasFront || $hasNone)
            ? [
                'sometimes',
                'json',
                function ($attribute, $value, $fail) {
                    if ($value === 'null') {
                        $fail($attribute . ' cannot be null.');
                    }

                    $decoded = json_decode($value, true);

                    if (empty($decoded)) {
                        $fail($attribute . ' cannot be empty.');
                    }
                }
            ]
            : ['nullable'];

        $designBackDataRules = $hasBack
            ? [
                'sometimes',
                'json',
                function ($attribute, $value, $fail) {
                    if ($value === 'null') {
                        $fail($attribute . ' cannot be null.');
                    }

                    $decoded = json_decode($value, true);

                    if (empty($decoded)) {
                        $fail($attribute . ' cannot be empty.');
                    }
                }
            ]
            : ['nullable'];

        return [
            'name.en' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'name.ar' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'design_data' => $designDataRules,
            'design_back_data' => $designBackDataRules,

            'base64_preview_image' => [
                Rule::requiredIf(($hasFront || $hasNone) && $isUpdatingFrontDesign),
                'nullable',
                'string',
            ],

            'back_base64_preview_image' => [
                Rule::requiredIf($hasBack && $isUpdatingBackDesign),
                'nullable',
                'string',
            ],

            'source_design_svg' => ['nullable', 'file', 'mimetypes:image/svg+xml', 'max:2048'],
            'colors' => ['sometimes', 'array'],
            'orientation' => ['sometimes', 'in:' . OrientationEnum::getValuesAsString()],
            'safety_area' => ['sometimes', 'numeric'],
            'border' => ['sometimes', 'numeric'],
            'go_to_editor' => ['sometimes', 'boolean'],
            'cut_margin' => ['sometimes', 'in:' . SafetyAreaEnum::getValuesAsString()],
            'font_styles_ids' => ['nullable', 'array'],
            'font_styles_ids.*' => ['integer', 'exists:font_styles,id'],
        ];
    }
}
