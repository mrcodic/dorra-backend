<?php

namespace App\Http\Requests\Template;

use App\Enums\Product\UnitEnum;
use App\Enums\Template\TypeEnum;
use App\Http\Requests\Base\BaseRequest;
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
            'name.ar' => ['required', 'string', 'max:255'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'type' => ['sometimes', 'in:' . TypeEnum::getValuesAsString()],
            'product_ids' => ['required', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'category_ids' => ['required', 'array'],
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
            'product_type' => ['sometimes', 'in:T-shirt,other'],
        ];
    }


}
