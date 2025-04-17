<?php

namespace App\Http\Requests\Product;

use App\Enums\Product\StatusEnum;
use App\Http\Requests\BaseRequest;
use App\Models\Category;
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'integer', 'exists:categories,id', function ($attribute, $value, $fail) {
                $category = Category::find($value);
                if (is_null($category->parent_id)) {
                    $fail('The selected category is a parent category, not a subcategory. Please select a valid subcategory.');
                }
            }],
            'tags' => ['nullable', 'array'],
            'has_custom_prices' => ['required', 'boolean'],
            'base_price' => [
                'required_if:has_custom_prices,false',
                'prohibited_if:has_custom_prices,true',
                'nullable',
                'numeric',
                'min:0',
            ],
            'prices' => [
                'array',
                'required_if:has_custom_prices,true',
                'prohibited_if:has_custom_prices,false',
            ],
            'prices.*.quantity' => ['required', 'integer', 'min:0'],
            'prices.*.price' => ['required', 'integer', 'min:0'],
            'specifications' => ['required', 'array'],
            'specifications.*.name' => ['required', 'string', 'max:255'],
            'specifications.*.specification_options' => ['required', 'array', 'min:1'],
            'specifications.*.specification_options.*.value' => ['required', 'string', 'max:255'],
            'specifications.*.specification_options.*.price' => ['nullable', 'numeric', 'min:0'],
            'specifications.*.specification_options.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'is_free_shipping' => ['required', 'boolean'],
            'status' => ['nullable', 'in:', StatusEnum::values()],
        ];

    }

    public function messages(): array
    {
        return [
            'name.required' => 'The product name is required.',
            'name.string' => 'The product name must be a valid string.',
            'name.max' => 'The product name cannot be longer than 255 characters.',

            'description.string' => 'The product description must be a valid string.',

            'image.required' => 'A main product image is required.',
            'image.image' => 'The main product image must be an image file.',
            'image.mimes' => 'The main image must be a file of type: jpg, jpeg, png.',
            'image.max' => 'The main image must not be larger than 2MB.',

            'images.array' => 'The product images must be an array.',
            'images.*.image' => 'Each product image must be a valid image.',
            'images.*.mimes' => 'Each image must be of type: jpg, jpeg, png.',
            'images.*.max' => 'Each image must not exceed 2MB.',

            'category_id.required' => 'Please select a category.',
            'category_id.integer' => 'The category must be a valid ID.',
            'category_id.exists' => 'The selected category does not exist.',

            'sub_category_id.integer' => 'The subcategory must be a valid ID.',
            'sub_category_id.exists' => 'The selected subcategory does not exist.',

            'tags.array' => 'Tags must be provided as an array.',

            'has_custom_prices.required' => 'Please specify whether the product has custom pricing.',
            'has_custom_prices.boolean' => 'Invalid value for custom pricing option.',

            'is_free_shipping.required' => 'Please specify whether the product includes free shipping.',
            'is_free_shipping.boolean' => 'Invalid value for free shipping option.',

            'status.in' => 'The selected status is invalid.',
        ];
    }

}
