<?php

namespace App\Http\Requests\Template;

use App\Enums\BorderEnum;
use App\Enums\OrientationEnum;
use App\Enums\SafetyAreaEnum;
use App\Enums\Template\TypeEnum;
use App\Http\Requests\Base\BaseRequest;

class StoreTranslatedTemplateRequest extends BaseRequest
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
        return [
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['required', 'string', 'max:255'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'type' => ['sometimes', 'in:' . TypeEnum::getValuesAsString()],
            'product_ids' => ['nullable','required_with:product_with_category', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'flags' => ['sometimes', 'array'],
            'flags.*' => ['integer', 'exists:flags,id'],
            'types' => ['required', 'array'],
            'types.*' => ['integer', 'exists:types,id'],
            'industry_ids' => ['nullable', 'array'],
            'industry_ids.*' => ['integer', 'exists:industries,id'],
            'template_image_id' => ['required','exists:media,id'],
            'design_data' => ['sometimes', 'json'],
            'design_back_data' => ['sometimes', 'json'],
            'base64_preview_image' => ['sometimes', 'string'],
            'back_base64_preview_image' => ['sometimes', 'string'],
            'specifications' => ['sometimes', 'array'],
            'specifications.*' => ['sometimes', 'integer', 'exists:product_specifications,id'],
            'source_design_svg' => ['nullable', 'file'],
            'orientation' => ['required', 'in:' . OrientationEnum::getValuesAsString()],
            'dimension_id' => ['nullable', 'integer', 'exists:dimensions,id'],
            'has_safety_area' => ['sometimes', 'in:0,1'],
            'has_corner' => ['sometimes', 'in:0,1'],
            'border' => ['sometimes', 'in:' . BorderEnum::getValuesAsString()],
            'safety_area' => ['sometimes', 'in:' . SafetyAreaEnum::getValuesAsString()],
        ];

    }


}
