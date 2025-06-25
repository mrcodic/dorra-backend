<?php

namespace App\Http\Requests\DiscountCode;

use App\Enums\DiscountCode\ScopeEnum;
use App\Enums\DiscountCode\TypeEnum;
use App\Http\Requests\Base\BaseRequest;

class StoreDiscountCodeRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:4', 'unique:discount_codes,code'],
            'type' => ['required', 'in:'.TypeEnum::getValuesAsString()],
            'value' => ['required', 'numeric', 'min:0'],
            'max_usage' => ['required', 'integer', 'min:1'],
            'expired_at' => ['required', 'date', 'after:today'],
            'number_of_discount_codes' => ['nullable', 'integer', 'min:1'],
            'scope' => ['required', 'in:'.ScopeEnum::getValuesAsString()],
            'product_ids' => ['required_if:scope,2', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'category_ids' => ['required_if:scope,1', 'array'],
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
