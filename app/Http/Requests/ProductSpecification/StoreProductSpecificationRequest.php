<?php

namespace App\Http\Requests\ProductSpecification;

use App\Enums\Product\StatusEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\Category;
use Illuminate\Validation\Rule;

class StoreProductSpecificationRequest extends BaseRequest
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
            'specifications' => ['sometimes', 'array'],
            'specifications.*.name_en' => 'sometimes|string',
            'specifications.*.name_ar' => 'sometimes|string',
            'specifications.*.specification_options' => ['sometimes', 'array', 'min:1'],
            'specifications.*.specification_options.*.value_en' => 'sometimes|string',
            'specifications.*.specification_options.*.value_ar' => 'sometimes|string',
            'specifications.*.specification_options.*.price' => ['nullable', 'numeric', 'min:0'],
            'specifications.*.specification_options.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ];
    }

}
