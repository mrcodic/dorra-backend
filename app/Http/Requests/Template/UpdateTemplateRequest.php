<?php

namespace App\Http\Requests\Template;

use App\Enums\BorderEnum;
use App\Enums\CornerEnum;
use App\Enums\OrientationEnum;
use App\Enums\SafetyAreaEnum;
use App\Enums\Template\StatusEnum;
use App\Enums\Template\TypeEnum;
use App\Http\Requests\Base\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateTemplateRequest extends BaseRequest
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
    public function rules($id): array
    {
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
            'description.en' => [
                'nullable',
                'string',
            ],
            'description.ar' => [
                'nullable',
                'string',
            ],
            'supported_languages' => ['nullable','array'],
            'supported_languages.*' => ['nullable','in:en,ar'],
            'price' => ['nullable', 'numeric','min:1'],
            'type' => ['sometimes','in:'.TypeEnum::getValuesAsString()],
//            'status' => ["required","integer","in:".StatusEnum::getValuesAsString()],

            'product_ids' => ['nullable','required_with:product_with_category', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'industry_ids' => ['nullable', 'array'],
            'industry_ids.*' => ['integer', 'exists:industries,id'],
            'mockup_ids' => ['nullable', 'array'],
            'mockup_ids.*' => ['nullable', 'exists:mockups,id'],
            'mockup_id' => ['nullable'  ,'integer', 'exists:mockups,id'],

            'template_image_front_id' => [
                Rule::requiredIf(fn()=> in_array(TypeEnum::FRONT->value, (array)request('types', []) )
                    && request('approach') == 'without_editor'),
                'nullable',
                'exists:media,id',
            ],

            'template_image_back_id' => [
                Rule::requiredIf(fn()=> in_array(TypeEnum::BACK->value, (array)request('types', []) )
                    && request('approach') == 'without_editor'&& request('use_front_as_back') == 0),
                'nullable',
                'exists:media,id',
            ],

            'template_image_none_id' => [
                Rule::requiredIf(fn()=> in_array(TypeEnum::NONE->value, (array)request('types', []) )
                    && request('approach') == 'without_editor'),
                'nullable',
                'exists:media,id',
            ],
            'template_image_id' => ['nullable','exists:media,id'],
            'design_data' => ['sometimes', 'json'],
            'base64_preview_image' => ['sometimes', 'string'],
            'specifications' => ['sometimes', 'array'],
            'specifications.*' => ['sometimes', 'integer', 'exists:product_specifications,id'],
            'source_design_svg' => ['nullable', 'file', 'mimetypes:image/svg+xml', 'max:2048'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['exists:tags,id'],
            'flags' => ['sometimes', 'array'],
            'flags.*' => ['integer', 'exists:flags,id'],
            'types' => ['required', 'array'],
            'types.*' => ['integer', 'exists:types,id'],
            'orientation' => ['sometimes', 'in:' . OrientationEnum::getValuesAsString()],
            'dimension_id' => [Rule::requiredIf(request('approach') == 'with_editor'), 'integer', 'exists:dimensions,id'],
            'go_to_editor' => ['sometimes', 'boolean'],
            'has_corner' => ['sometimes', 'in:0,1'],
            'has_safety_area' => ['sometimes', 'in:0,1'],
            'border' => ['nullable', 'in:' . CornerEnum::getValuesAsString()],
            'safety_area' => ['sometimes', 'in:' . SafetyAreaEnum::getValuesAsString()],
            'cut_margin' => ['sometimes', 'in:' . SafetyAreaEnum::getValuesAsString()],
//            'colors' => ['sometimes','array'],
//            'colors.*.value' => ['sometimes','string'],
//            'colors.*.image_id' => ['sometimes', 'integer', 'exists:media,id'],
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
