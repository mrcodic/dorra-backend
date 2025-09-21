<?php

namespace App\Http\Requests\Design;

use App\Enums\OrientationEnum;
use App\Enums\Product\UnitEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\{Category, Dimension, Guest, Product, Template};
use App\Rules\DimensionWithinUnitRange;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDesignRequest extends BaseRequest
{
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
    }
    public function rules(): array
    {
        return [
            'template_id' => ['bail', 'sometimes', 'exists:templates,id'],
            'product_id'   => ['required', 'integer',function ($attribute, $value, $fail) {
                $template = Template::find($this->template_id);
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
                if (!($template->products->pluck('id')->contains($value))&& $this->input('designable_type') == 'product') {
                    return $fail("The selected category is not associated with the selected template.");
                } if (!($template->categories->pluck('id')->contains($value)) && $this->input('designable_type') == 'category') {
                    return $fail("The selected product is not associated with the selected template.");
                }
            }],
            'designable_type' => ['required', 'string', 'in:App\\Models\\Product,App\\Models\\Category'],
            'user_id' => ['nullable', 'exists:users,id'],
            'guest_id' => ['nullable', 'exists:guests,id'],
            'dimension_id' => [
                'nullable',
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
            'design_data' => ['nullable', 'json'],
            'design_back_data' => ['nullable', 'json'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'product_price_id' => [
                Rule::requiredIf(function () {
                    $product = Product::find($this->product_id) ?? Category::find($this->product_id);
                    return $product && $product->prices()->exists();
                }),
                'exists:product_prices,id',
            ],
            "specs" => ["sometimes", "array"],
            "specs.*.id" => ["sometimes", "exists:product_specifications,id"],
            "specs.*.option" => ["sometimes", "exists:product_specification_options,id"],
            'orientation' => ['sometimes', 'in:' . OrientationEnum::getValuesAsString()],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $templateId = $this->input('template_id');

            if ($templateId) {
                $template = Template::find($templateId);
                if (!$template || $template->media->isEmpty()) {
                    $validator->errors()->add('template_id', 'The selected template does not have any media attached.');
                }
            }
        });
    }

    protected function passedValidation()
    {
        $template = Template::find($this->template_id);
        $cookieValue = getCookie('cookie_id')['value'];
        $activeGuard = getActiveGuard();


        $userId = match ($activeGuard) {
            'web' => $this->input('user_id'),
            'sanctum' => Auth::guard('sanctum')->id(),
            default => null,
        };


        $guestId = null;
        if (!$userId && $cookieValue) {
            $guest = Guest::firstOrCreate(['cookie_value' => $cookieValue]);
            $guestId = $guest->id;
        }

        $this->merge([
            'user_id' => $userId,
            'guest_id' => $guestId,
            'designable_id' => $this->product_id,
            'design_data' => $template?->design_data ?? $this->input('design_data'),
            'design_back_data' => $template?->design_back_data ?? $this->input('design_back_data'),
            'name' => $template?->name ?? $this->input('name'),
            'description' => $template?->description ?? $this->input('description'),
            'cookie' => $cookieValue,
        ]);
    }
}
