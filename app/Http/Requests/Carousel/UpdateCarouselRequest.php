<?php

namespace App\Http\Requests\Carousel;

use App\Http\Requests\Base\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateCarouselRequest extends BaseRequest
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
            'title.en' => [
                'required',
                'string',
                'max:255',
            ],
            'title.ar' => [
                'required',
                'string',
                'max:255',
            ],
            'subtitle.en' => [
                'required',
                'string',
                'max:255',
            ],
            'subtitle.ar' => [
                'required',
                'string',
                'max:255',
            ],
            'product_id' =>['required', 'integer', 'exists:products,id'],
            'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,svg'],
        ];
    }


}
