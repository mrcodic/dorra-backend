<?php

namespace App\Http\Requests\Template;

use App\Enums\Template\TypeEnum;
use App\Enums\Template\UnitEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Rules\DimensionWithinUnitRange;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use App\Models\Product;

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
            'unit' => [
                Rule::requiredIf($this->input('product_type') === 'other'),
                'integer',
                'in:' . UnitEnum::getValuesAsString(),
            ],
            'height' => [
                Rule::requiredIf($this->input('product_type') === 'other'),
                'numeric',
                new DimensionWithinUnitRange()
            ],
            'width' => [
                Rule::requiredIf($this->input('product_type') === 'other'),
                'numeric',
                new DimensionWithinUnitRange()
            ],

            'product_id' => [
                Rule::requiredIf($this->input('product_type') === 'other'),
                'prohibits:product_type:T-shirt',
                'exists:products,id'
            ],
            'design_data' => ['sometimes', 'json'],
            'base64_preview_image' => ['sometimes', 'string'],
            'specifications' => ['sometimes', 'array'],
            'specifications.*' => ['sometimes', 'integer', 'exists:product_specifications,id'],
            'source_design_svg' => ['nullable', 'file'],
            'product_type' => ['sometimes', 'in:T-shirt,other'],
        ];
    }


}
