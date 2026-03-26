<?php

namespace App\Http\Requests\Template;

use App\Enums\BorderEnum;
use App\Enums\CornerEnum;
use App\Enums\OrientationEnum;
use App\Enums\Product\UnitEnum;
use App\Enums\SafetyAreaEnum;
use App\Enums\Template\TypeEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\Dimension;
use App\Rules\DimensionWithinUnitRange;
use Illuminate\Validation\Rule;

class StoreTemplateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['nullable', 'string', 'max:255'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric','min:1'],
            'supported_languages' => ['nullable','array'],
            'supported_languages.*' => ['nullable','in:en,ar'],
            'type' => ['sometimes', 'in:' . TypeEnum::getValuesAsString()],
            'product_ids' => ['nullable','required_with:product_with_category', 'array'],
            'industry_ids' => ['nullable', 'array'],
            'industry_ids.*' => ['integer', 'exists:industries,id'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'mockup_ids' => ['nullable', 'array'],
            'mockup_ids.*' => ['integer', 'exists:mockups,id'],
            'mockup_id' => ['nullable'  ,'integer', 'exists:mockups,id'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'flags' => ['sometimes', 'array'],
            'flags.*' => ['integer', 'exists:flags,id'],
            'types' => ['required', 'array'],
            'types.*' => ['integer', 'exists:types,id'],
            'approach' => ['sometimes', 'in:with_editor,without_editor'],
            'template_image_front_id' => [
                Rule::requiredIf(fn()=> in_array(TypeEnum::FRONT->value, (array)request('types', []) )
                    && request('approach') == 'without_editor'),
                'nullable',
                'exists:media,id',
            ],

            'template_image_back_id' => [
                Rule::requiredIf(fn()=> in_array(TypeEnum::BACK->value, (array)request('types', []) )
                    && request('approach') == 'without_editor'),
                'nullable',
                'exists:media,id',
            ],

            'template_image_none_id' => [
                Rule::requiredIf(fn()=> in_array(TypeEnum::NONE->value, (array)request('types', []), )
                    && request('approach') == 'without_editor'),
                'nullable',
                'exists:media,id',
            ],
            'template_image_id' => ['nullable','exists:media,id'],
            'design_data' => ['sometimes', 'json', function ($attribute, $value, $fail) {
                if ($value === 'null') {
                    $fail($attribute . ' cannot be null.');
                }

                $decoded = json_decode($value, true);
                if (empty($decoded)) {
                    $fail($attribute . ' cannot be empty.');
                }
            },],
            'design_back_data' => ['sometimes', 'json', function ($attribute, $value, $fail) {
                if ($value === 'null') {
                    $fail($attribute . ' cannot be null.');
                }

                $decoded = json_decode($value, true);
                if (empty($decoded)) {
                    $fail($attribute . ' cannot be empty.');
                }
            },],
            'base64_preview_image' => ['sometimes', 'string'],
            'back_base64_preview_image' => ['sometimes', 'string'],
            'specifications' => ['sometimes', 'array'],
            'specifications.*' => ['sometimes', 'integer', 'exists:product_specifications,id'],
            'source_design_svg' => ['nullable', 'file'],
            'orientation' => ['sometimes', 'in:' . OrientationEnum::getValuesAsString()],
            'dimension_id' => [Rule::requiredIf(request('q') == 'with'), 'integer', 'exists:dimensions,id'],
//            'colors' => ['sometimes','array'],
//            'colors.*.value' => ['sometimes','string'],
//            'colors.*.image_id' => ['sometimes', 'integer', 'exists:media,id'],
            'has_corner' => ['sometimes', 'in:0,1'],
            'has_safety_area' => ['sometimes', 'in:0,1'],
            'border' => ['sometimes', 'in:' . CornerEnum::getValuesAsString()],
            'safety_area' => ['sometimes', 'in:' . SafetyAreaEnum::getValuesAsString()],
            'cut_margin' => ['sometimes', 'in:' . SafetyAreaEnum::getValuesAsString()],

        ];
    }
    public function messages(): array
    {
        return [
            'dimension_id.required' => 'You must choose size',
            'dimension_id.integer'  => 'You must choose size',
            'dimension_id.exists'   => 'Selected size is invalid',
        ];
    }

}
