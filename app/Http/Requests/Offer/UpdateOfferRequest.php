<?php

namespace App\Http\Requests\Offer;

use App\Http\Requests\Base\BaseRequest;

class UpdateOfferRequest extends BaseRequest
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
            'name.en' => [
                'required',
                'string',
                'max:255',
            ],
            'name.ar' => [
                'nullable',
                'string',
                'max:255',
            ],
            'value' => ['required', 'numeric', 'min:0'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'product_ids' => ['required_without:category_ids', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],

            'category_ids' => ['required_without:product_ids', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_ids.*.exists' => 'One or more selected products are invalid.',
            'category_ids.*.exists' => 'One or more selected categories are invalid.',
        ];
    }
}
