<?php

namespace App\Http\Requests\DiscountCode;

use App\Enums\DiscountCode\ScopeEnum;
use App\Enums\DiscountCode\TypeEnum;
use App\Http\Requests\Base\BaseRequest;

use Illuminate\Validation\Rule;

class UpdateDiscountCodeRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules($id): array
    {

        return [
            'code' => [
                'required',
                'string',
                'max:4',
                Rule::unique('discount_codes', 'code')->ignore($id),
            ],
            'type' => ['required', 'in:' . TypeEnum::getValuesAsString()],
            'value' => ['required', 'numeric', 'min:0'],
            'max_usage' => ['nullable', 'integer', 'min:1'],
            'expired_at' => ['nullable', 'date', 'after:today'],
            'scope' => ['required', 'in:' . ScopeEnum::getValuesAsString()],
            'product_ids' => ['required_if:scope,1', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'category_ids' => ['required_if:scope,2', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'This discount code has already been used.',
            'expired_at.after' => 'The expiration date must be in the future.',
            'product_ids.required_if' => 'Please select at least one product.',
            'category_ids.required_if' => 'Please select at least one category.',
        ];
    }
}
