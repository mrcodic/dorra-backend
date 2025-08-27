<?php

namespace App\Http\Requests\Review;

use App\Http\Requests\Base\BaseRequest;

class StoreReviewRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {

            $this->merge([
                'user_id' => auth('sanctum')->user()->id,
            ]);

    }
    public function rules(): array
    {
        return [
            'review'          => ['required', 'string', 'min:5'],
            'rating'          => ['required', 'integer', 'between:1,5'],
            'reviewable_id'   => ['required', 'integer','exists:products,id'],
            'images'          => ['nullable', 'array'],
            'images.*'        => ['nullable', 'mimes:jpeg,jpg,png,gif,svg', 'max:2048'],
        ];
    }



}
