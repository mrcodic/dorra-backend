<?php

namespace App\Http\Requests\Design;

use App\Enums\OrientationEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\Category;
use App\Models\CountryCode;
use App\Models\Design;
use App\Models\Dimension;
use App\Models\Product;
use App\Models\Template;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;

class UpdateDesignRequest extends BaseRequest
{
    /**
     * Determine if the v1 is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $map = [
            'product'  => \App\Models\Product::class,
            'category' => \App\Models\Category::class,
        ];

        if (isset($map[$this->designable_type])) {
            $this->merge([
                'designable_type' => $map[$this->designable_type],
            ]);
        }
        if ($this->has('name')) {
            $name = $this->input('name');
            $this->merge([
                'name' => [
                    'en' => $name,
                    'ar' => $name,
                ],
            ]);
        }
        if ($this->has('product_id')) {
            $this->merge([
                'designable_id' => $this->product_id,

            ]);
        }
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'design_data' => ['sometimes', 'json'],
            'design_back_data' => ['sometimes', 'json'],
            'base64_preview_image' => ['sometimes', 'string', 'required_without:design_image'],
            'back_base64_preview_image' => ['sometimes', 'string'],
            'design_image' => ['sometimes', 'file', 'mimetypes:image/svg+xml', 'max:2048', 'required_without:base64_preview_image'],
            'name' => ['sometimes'],
            'description' => ['sometimes','string','max:1000'],
            'product_price_id' => [
                Rule::requiredIf(function () {
                    $product = Product::find($this->product_id) ?? Category::find($this->product_id);
                    return $product && $product->prices()->exists();
                }),
                'exists:product_prices,id',
            ],
            'product_id'   => ['nullable', 'integer',function ($attribute, $value, $fail) {
                $template = Design::find($this->route('design'))?->template;
                $category = Category::find($value) ?? Product::find($value);
                if (!$template) {
                    return;
                } if (!$category) {
                    return;
                }
                if ($category->is_has_category && $this->designable_type == Category::class)
                {
                    return $fail("You Cannot add product with categories.");
                }
                if (!($template?->products->pluck('id')->contains($value))&& $this->input('designable_type') == 'product') {
                    return $fail("The selected category is not associated with the selected template.");
                } if (!($template?->categories->pluck('id')->contains($value)) && $this->input('designable_type') == 'category') {
                    return $fail("The selected product is not associated with the selected template.");
                }
            }],
            'designable_type' => ['nullable', 'string', 'in:App\\Models\\Product,App\\Models\\Category'],

            'dimension_id' => [
                'sometimes',
                'exists:dimensions,id',
                function ($attribute, $value, $fail) {
                    if (!$value) {
                        return;
                    }
                    $dimension = Dimension::find($value);

                    if ($this->designable_type === Product::class && !$dimension?->products()->exists()) {
                        return $fail("This dimension is not linked to any product.");
                    }

                    if ($this->designable_type === Category::class && !$dimension?->categories()->exists()) {
                        return $fail("This dimension is not linked to any category.");
                    }
                },
            ],
            "specs" => ["sometimes", "array"],
            "specs.*.id" => ["sometimes", "exists:product_specifications,id"],
            "specs.*.option" => ["sometimes", "exists:product_specification_options,id"],
            'orientation' => ['sometimes', 'in:' . OrientationEnum::getValuesAsString()],
        ];
    }


}
