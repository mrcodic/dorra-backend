<?php

namespace App\Http\Requests\Template;

use App\Enums\OrientationEnum;
use App\Enums\SafetyAreaEnum;
use App\Enums\Template\StatusEnum;
use App\Enums\Template\TypeEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\CountryCode;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;

class UpdateTemplateEditorRequest extends BaseRequest
{
    /**
     * Determine if the v1 is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $template = $this->route('template');
        $types = $template?->types->pluck('value')->map(fn($t) => $t->value)->toArray() ?? [];

        $hasFront = in_array(TypeEnum::FRONT->value, $types);
        $hasBack  = in_array(TypeEnum::BACK->value, $types);
        $hasNone  = in_array(TypeEnum::NONE->value, $types);

        $designDataRules = ($hasFront || $hasNone)
            ? ['required_with:design_data', 'json', function ($attribute, $value, $fail) {
                if ($value === 'null') {
                    $fail($attribute . ' cannot be null.');
                }
                if (empty(json_decode($value, true))) {
                    $fail($attribute . ' cannot be empty.');
                }
            }]
            : ['nullable'];

        $designBackDataRules = $hasBack
            ? ['required_with:design_back_data', 'json', function ($attribute, $value, $fail) {
                if ($value === 'null') {
                    $fail($attribute . ' cannot be null.');
                }
                if (empty(json_decode($value, true))) {
                    $fail($attribute . ' cannot be empty.');
                }
            }]
            : ['nullable'];

        $previewImageRules     = ($hasFront || $hasNone)
            ? ['required_with:base64_preview_image', 'string']
            : ['nullable', 'string'];

        $backPreviewImageRules = $hasBack
            ? ['required_with:back_base64_preview_image', 'string']
            : ['nullable', 'string'];

        return [
            'name.en'                    => ['sometimes', 'string', 'max:255'],
            'name.ar'                    => ['sometimes', 'string', 'max:255'],
            'design_data'                => $designDataRules,
            'design_back_data'           => $designBackDataRules,
            'base64_preview_image'       => $previewImageRules,
            'back_base64_preview_image'  => $backPreviewImageRules,
            'source_design_svg'          => ['nullable', 'file', 'mimetypes:image/svg+xml', 'max:2048'],
            'colors'                     => ['sometimes', 'array'],
            'orientation'                => ['sometimes', 'in:' . OrientationEnum::getValuesAsString()],
            'safety_area'                => ['sometimes', 'numeric'],
            'border'                     => ['sometimes', 'numeric'],
            'go_to_editor'               => ['sometimes', 'boolean'],
            'cut_margin'                 => ['sometimes', 'in:' . SafetyAreaEnum::getValuesAsString()],
            'font_styles_ids'            => ['nullable', 'array'],
            'font_styles_ids.*'          => ['integer', 'exists:font_styles,id'],
        ];
    }


}
