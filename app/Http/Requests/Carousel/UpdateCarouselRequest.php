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
    public function rules(): array
    {
        return [
            'carousels' => 'required|array',
            'carousels.*.id' => ['nullable', 'integer', 'exists:carousels,id'],
            'carousels.*.title_en' => ['required', 'string', 'max:255'],
            'carousels.*.title_ar' => ['required', 'string', 'max:255'],
            'carousels.*.subtitle_en' => ['required', 'string', 'max:255'],
            'carousels.*.subtitle_ar' => ['required', 'string', 'max:255'],
            'carousels.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'carousels.*.image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,svg'],
            'carousels.*.mobile_image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,svg'],
        ];
    }



}
