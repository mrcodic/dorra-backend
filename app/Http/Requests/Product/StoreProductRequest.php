<?php

namespace App\Http\Requests\Product;

use App\Enums\Product\StatusEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\Category;
use Illuminate\Validation\Rule;

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
            'name.en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name->en'),
            ],
            'name.ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name->ar'),
            ],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'integer', 'exists:categories,id', function ($attribute, $value, $fail) {
                $category = Category::find($value);
                if (is_null($category->parent_id)) {
                    $fail('The selected category is a parent category, not a subcategory. Please select a valid subcategory.');
                }
            }],
            'tags' => ['nullable', 'array'],
            'has_custom_prices' => ['required', 'boolean'],
            'has_mockup' => ['required', 'boolean'],
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
            'specifications' => ['sometimes', 'array'],
            'specifications.*.name_en' => 'sometimes|string',
            'specifications.*.name_ar' => 'sometimes|string',
            'specifications.*.specification_options' => ['sometimes', 'array', 'min:1'],
            'specifications.*.specification_options.*.value_en' => 'sometimes|string',
            'specifications.*.specification_options.*.value_ar' => 'sometimes|string',
            'specifications.*.specification_options.*.price' => ['nullable', 'numeric', 'min:0'],
            'specifications.*.specification_options.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png'],
            'is_free_shipping' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:', StatusEnum::values()],
        ];
    }

    /**
     * Get the custom validation messages for the request.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.en.required' => 'The English name is required.',
            'name.en.string' => 'The English name must be a valid string.',
            'name.en.max' => 'The English name must not exceed 255 characters.',
            'name.en.unique' => 'This English name already exists.',

            'name.ar.required' => 'The Arabic name is required.',
            'name.ar.string' => 'The Arabic name must be a valid string.',
            'name.ar.max' => 'The Arabic name must not exceed 255 characters.',
            'name.ar.unique' => 'This Arabic name already exists.',

            'description.en.nullable' => 'The English description is optional.',
            'description.en.string' => 'The English description must be a valid string.',

            'description.ar.nullable' => 'The Arabic description is optional.',
            'description.ar.string' => 'The Arabic description must be a valid string.',

            'image.required' => 'Please upload an image.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg.',
            'image.max' => 'The main product image must not be larger than 2MB.',

            'images.array' => 'The product images must be an array.',
            'images.*.image' => 'Each product image must be a valid image.',
            'images.*.mimes' => 'Each image must be of type: jpg, jpeg, png.',
            'images.*.max' => 'Each image must not exceed 2MB.',

            'category_id.required' => 'Please select a category.',
            'category_id.integer' => 'The category must be a valid ID.',
            'category_id.exists' => 'The selected category does not exist.',

            'sub_category_id.integer' => 'The subcategory must be a valid ID.',
            'sub_category_id.exists' => 'The selected subcategory does not exist.',
            'sub_category_id.custom' => 'The selected category is a parent category, not a subcategory. Please select a valid subcategory.',

            'tags.array' => 'Tags must be provided as an array.',

            'has_custom_prices.required' => 'Please specify whether the product has custom pricing.',
            'has_custom_prices.boolean' => 'Invalid value for custom pricing option.',

            'is_free_shipping.required' => 'Please specify whether the product includes free shipping.',
            'is_free_shipping.boolean' => 'Invalid value for free shipping option.',

            'status.in' => 'The selected status is invalid.',
        ];
    }

    /**
     * Get the custom validation attributes for the request.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name.en' => 'English Name',
            'name.ar' => 'Arabic Name',
            'description.en' => 'English Description',
            'description.ar' => 'Arabic Description',
            'image' => 'Product Image',
            'category_id' => 'Category',
            'sub_category_id' => 'Subcategory',
            'tags' => 'Product Tags',
            'has_custom_prices' => 'Custom Prices',
            'base_price' => 'Base Price',
            'prices' => 'Price Details',
            'specifications' => 'Product Specifications',
            'is_free_shipping' => 'Free Shipping',
            'status' => 'Product Status',
        ];
    }


}
