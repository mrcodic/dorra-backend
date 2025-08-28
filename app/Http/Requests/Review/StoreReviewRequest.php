<?php

namespace App\Http\Requests\Review;

use App\Http\Requests\Base\BaseRequest;
use App\Models\Product;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'review' => ['required', 'string', 'min:5'],
            'rating' => ['required', 'between:1,5'],
            'reviewable_id' => ['required', 'exists:products,id', Rule::unique('reviews', 'reviewable_id')
                ->where(fn($q) => $q
                    ->where('reviewable_type', Product::class)
                    ->where('user_id', auth()->id())
                ),
            ],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'mimes:jpeg,jpg,png,gif,svg', 'max:2048'],
        ];
    }


}
