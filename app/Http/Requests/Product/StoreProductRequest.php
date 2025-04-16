<?php

namespace App\Http\Requests\Product;

use App\Enums\Product\StatusEnum;
use App\Http\Requests\BaseRequest;
use App\Models\CountryCode;

class StoreProductRequest extends BaseRequest
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
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'image'            => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'images'            => ['nullable', 'array'],
            'images.*'          => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'category_id'      => ['required', 'integer', 'exists:categories,id'],
            'sub_category_id'  => ['nullable', 'integer', 'exists:sub_categories,id'],
            'tags'              => ['nullable', 'array'],
            'has_custom_prices'=> ['required', 'boolean'],
            'is_free_shipping' => ['required', 'boolean'],
//            'base_price'       => ['required', 'numeric', 'min:0'],
            'status'           => ['nullable','in:',StatusEnum::values()],
        ];

    }


}
