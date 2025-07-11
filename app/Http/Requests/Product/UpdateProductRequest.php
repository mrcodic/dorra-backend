<?php

namespace App\Http\Requests\Product;

use App\Enums\Product\StatusEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\Category;
use App\Models\CountryCode;
use App\Models\Product;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends BaseRequest
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
    public function rules($id): array
    {
        return [
            'name.en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name->en')->ignore($id),
            ],
            'name.ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name->ar')->ignore($id),
            ],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'image' => ['image', 'mimes:jpg,jpeg,png',
                Rule::requiredIf(function () use($id){
                   return !Product::find($id)->getFirstMedia("product_main_image");
                })
                ],
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
            'prices.*.quantity' => ['nullable', 'integer', 'min:0'],
            'prices.*.price' => ['nullable', 'integer', 'min:0'],
            'specifications' => ['sometimes', 'array'],
            'specifications.*.name_en' => 'sometimes|string',
            'specifications.*.name_ar' => 'sometimes|string',
            'specifications.*.specification_options' => ['sometimes', 'array', 'min:1'],
            'specifications.*.specification_options.*.value_en' => 'sometimes|string',
            'specifications.*.specification_options.*.value_ar' => 'sometimes|string',
            'specifications.*.specification_options.*.price' => ['nullable', 'numeric', 'min:0'],
            'specifications.*.specification_options.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
//            'is_free_shipping' => ['required', 'boolean'],
            'status' => ['nullable', 'in:', StatusEnum::values()],
        ];


    }


}
