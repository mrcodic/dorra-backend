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
//                Rule::unique('products', 'name->en')->ignore($id),
            ],
            'name.ar' => [
                'required',
                'string',
                'max:255',
//                Rule::unique('products', 'name->ar')->ignore($id),
            ],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'image_id' => ['required', 'exists:media,id'],
            'image_model_id' => ['required', 'exists:media,id'],

            'images_ids' => ['nullable', 'array'],
            'images_ids.*' => ['nullable','exists:media,id'],
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
                'min:1',
            ],
            'prices' => [
                'array',
                'required_if:has_custom_prices,true',
                'prohibited_if:has_custom_prices,false',
            ],
            'prices.*.quantity' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('product_prices', 'quantity')
                    ->where(fn ($query) => $query
                        ->where('pricable_id', $id)
                        ->where('pricable_type', Product::class)
                    )->ignore($id,'pricable_id'),
            ],
            'prices.*.price' => ['nullable', 'integer', 'min:1'],
            'specifications' => ['sometimes', 'array'],
            'specifications.*.name_en' => 'sometimes|string',
            'specifications.*.name_ar' => 'sometimes|string',
            'specifications.*.specification_options' => ['sometimes', 'array', 'min:1'],
            'specifications.*.specification_options.*.value_en' => 'sometimes|string',
            'specifications.*.specification_options.*.value_ar' => 'sometimes|string',
            'specifications.*.specification_options.*.price' => ['nullable', 'numeric', 'min:1'],
            'specifications.*.specification_options.*.option_image' => ['nullable', 'exists:media,id'],
//            'is_free_shipping' => ['required', 'boolean'],
            'dimensions'=>['required_without:custom_dimensions', 'array'],
            'dimensions.*' =>['sometimes', 'integer', 'exists:dimensions,id'],
            'custom_dimensions'=>['required_without:dimensions', 'array'],
            'custom_dimensions.*' =>['sometimes'],
            'status' => ['nullable', 'in:', StatusEnum::values()],
            'show_add_cart_btn' => ['required', 'boolean'],
            
        ];


    }


}
