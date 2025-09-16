<?php

namespace App\Http\Requests\Template;

use App\Enums\OrientationEnum;
use App\Enums\Product\UnitEnum;
use App\Enums\Template\TypeEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Rules\DimensionWithinUnitRange;


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
            'product_ids' => ['required_without:category_ids','required_with:product_with_category', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'category_ids' => ['required_without:product_ids', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'types' => ['required', 'array'],
            'types.*' => ['integer', 'exists:types,id'],
            'template_image_id' => ['required','exists:media,id'],
            'design_data' => ['sometimes', 'json'],
            'design_back_data' => ['sometimes', 'json'],
            'base64_preview_image' => ['sometimes', 'string'],
            'back_base64_preview_image' => ['sometimes', 'string'],
            'specifications' => ['sometimes', 'array'],
            'specifications.*' => ['sometimes', 'integer', 'exists:product_specifications,id'],
            'source_design_svg' => ['nullable', 'file'],
            'orientation' => ['required', 'in:' . OrientationEnum::getValuesAsString()],

        ];

    }


}
