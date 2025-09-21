<?php

namespace App\Http\Requests\Product;

use App\Enums\Product\StatusEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\Category;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class StoreProductRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }


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
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'image_id' => ['required', 'exists:media,id'],
            'image_model_id' => ['required', 'exists:media,id'],
            'images_ids' => ['nullable', 'array'],
            'images_ids.*' => ['nullable','exists:media,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'sub_category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    $category = Category::find($value);
                    if ($category && is_null($category->parent_id)) {
                        $fail('The selected category is a parent category, not a subcategory. Please select a valid subcategory.');
                    }
                }
            ],
            'tags' => ['nullable', 'array'],
            'has_custom_prices' => ['required', 'boolean'],
            'has_mockup' => ['required', 'boolean'],
            'base_price' => [
                'required_if:has_custom_prices,false',
                'prohibited_if:has_custom_prices,true',
                'nullable',
                'numeric',
                'min:1',
            ],
            'prices' => [
                'array',
                'required_if:has_custom_prices,true',
                'prohibited_if:has_custom_prices,false',
            ],
            'prices.*.quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $price = request()->input("prices.$index.price");

                    $pairs = request()->attributes->get('price_pairs', []);
                    $key = $value . '-' . $price;

                    if (in_array($key, $pairs, true)) {
                        $fail("Duplicate quantity/price combination found.");
                    }

                    $pairs[] = $key;
                    request()->attributes->set('price_pairs', $pairs);
                }
            ],
            'prices.*.price' => [
                'required',
                'integer',
                'min:1',
            ],

            'specifications' => ['sometimes', 'array'],
            'specifications.*.name_en' => 'sometimes|string',
            'specifications.*.name_ar' => 'sometimes|string',
            'specifications.*.specification_options' => ['required_with:specifications', 'array', 'min:1'],
            'specifications.*.specification_options.*.value_en' => 'required_with:specifications|string',
            'specifications.*.specification_options.*.value_ar' => 'required_with:specifications|string',
            'specifications.*.specification_options.*.price' => ['nullable', 'numeric', 'min:1'],
            'specifications.*.specification_options.*.option_image' => ['nullable', 'exists:media,id'],
            'dimensions'=>['required_without:custom_dimensions', 'array'],
            'dimensions.*' =>['sometimes', 'integer', 'exists:dimensions,id'],
            'custom_dimensions'=>['required_without:dimensions', 'array'],
            'custom_dimensions.*' =>['sometimes'],
            'is_free_shipping' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:', StatusEnum::values()],
            'show_add_cart_btn' => ['required', 'boolean'],
            'show_customize_design_btn' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'prices.*.quantity.required' => 'Quantity is required.',
            'prices.*.quantity.integer'  => 'Quantity must be a number.',
            'prices.*.quantity.min'      => 'Quantity must be at least 1.',
            'prices.*.price.required'    => 'Price is required.',
            'prices.*.price.integer'     => 'Price must be a number.',
            'prices.*.price.min'         => 'Price must be at least 1.',
        ];
    }

    public function attributes(): array
    {
        return [
            'prices.*.quantity' => 'Quantity',
            'prices.*.price'    => 'Price',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $prices = $this->input('prices', []);
            $pairs = [];

            foreach ($prices as $index => $price) {
                $quantity = $price['quantity'] ?? null;
                $value    = $price['price'] ?? null;

                if ($quantity !== null && $value !== null) {
                    $key = $quantity . '-' . $value;

                    if (in_array($key, $pairs, true)) {
                        $validator->errors()->add(
                            "prices.$index",
                            "Duplicate quantity/price combination found."
                        );
                    }

                    $pairs[] = $key;
                }
            }
        });
    }


}
