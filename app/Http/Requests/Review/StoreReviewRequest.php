<?php

namespace App\Http\Requests\Review;

use App\Http\Requests\Base\BaseRequest;
use App\Models\Product;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends BaseRequest
{
    protected function prepareForValidation()
    {
        $map = [
            'product'  => \App\Models\Product::class,
            'category' => \App\Models\Category::class,
        ];

        if (isset($map[$this->reviewable_type])) {
            $this->merge([
                'reviewable_type' => $map[$this->reviewable_type],
            ]);
        }
    }
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'review' => ['required', 'string', 'min:5'],
            'rating' => ['required', 'between:1,5'],
            'reviewable_id' => ['required',  Rule::unique('reviews', 'reviewable_id')
                ->where(fn($q) => $q
                    ->where('reviewable_type', Product::class)
                    ->where('user_id', auth()->id())
                ),
            ],
            'reviewable_type' => ['required', 'string', 'in:App\\Models\\Product,App\\Models\\Category'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'mimes:jpeg,jpg,png,gif,svg', 'max:2048'],
        ];
    }


}
