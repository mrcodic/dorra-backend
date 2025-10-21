<?php

namespace App\Http\Requests\Offer;

use App\Enums\Offer\TypeEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;

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
    public function rules($id): array
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
            'type' => ['required', 'in:'.TypeEnum::getValuesAsString()],

            'start_at' => ['required', 'date',  'after_or_equal:today'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'product_ids'   => ['nullable', 'array'],
            'product_ids.*' => [
                'integer',
                'distinct',
                'exists:products,id',
                function ($attribute, $value, $fail)  use($id){
                    $overlaps = Product::whereKey($value)
                        ->whereHas('offers', function ($q) use ($id) {
                            $q->where('offers.id', '!=', $id)
                                ->orWhere('offers.end_at', '<=', now());
                        })
                        ->exists();
                    if ($overlaps) {
                        $name = Product::whereKey($value)->value('name') ?? "#{$value}";
                        $fail("Category \"{$name}\" is already included in another offer.");
                    }
                },
            ],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id',function ($attribute, $value, $fail) use ($id) {
                $overlaps = Category::whereKey($value)
                    ->whereHas('offers', function ($q) use ($id) {
                        $q->where('offers.id', '!=', $id)
                            ->orWhere('offers.end_at', '<=', now());
                    })
                    ->exists();
                if ($overlaps) {
                    $name = Category::whereKey($value)->value('name') ?? "#{$value}";
                    $fail("Product \"{$name}\" is already included in another offer.");
                }
            }],
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
