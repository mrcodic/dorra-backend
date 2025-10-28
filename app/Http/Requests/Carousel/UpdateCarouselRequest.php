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
            'carousels.*.title_en' => ['nullable', 'string', 'max:255'],
            'carousels.*.title_ar' => ['nullable', 'string', 'max:255'],
            'carousels.*.subtitle_en' => ['nullable', 'string', 'max:255'],
            'carousels.*.subtitle_ar' => ['nullable', 'string', 'max:255'],
            'carousels.*.product_id' => [ 'required_without:carousels.*.category_id',
                'prohibited_with:carousels.*.category_id',
                'integer', 'exists:products,id'],
            'carousels.*.category_id' => [ 'required_without:carousels.*.product_id',
                'prohibited_with:carousels.*.product_id',
                'integer', 'exists:categories,id'],
            'carousels.*.website_media_ids' => ['sometimes','exists:media,id'],
            'carousels.*.website_ar_media_ids' => ['sometimes','exists:media,id',],
            'carousels.*.mobile_media_ids' => ['sometimes','exists:media,id',],
            'carousels.*.mobile_ar_media_ids' => ['sometimes','exists:media,id',],
            'carousels.*.title_color' => ['nullable', 'starts_with:#', 'regex:/^#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?$/'],
            'carousels.*.subtitle_color' => ['nullable', 'starts_with:#', 'regex:/^#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?$/'],

        ];
    }
    public function messages(): array
    {
        return [
            'carousels.required' => 'At least one carousel item is required.',
            'carousels.array' => 'Carousels data must be sent as an array.',

            'carousels.*.id.integer' => 'Carousel ID must be a valid number.',
            'carousels.*.id.exists' => 'The selected carousel does not exist.',

            'carousels.*.title_en.required' => 'The English title is required.',
            'carousels.*.title_en.string' => 'The English title must be a text.',
            'carousels.*.title_en.max' => 'The English title may not be longer than 255 characters.',

            'carousels.*.title_ar.required' => 'The Arabic title is required.',
            'carousels.*.title_ar.string' => 'The Arabic title must be a text.',
            'carousels.*.title_ar.max' => 'The Arabic title may not be longer than 255 characters.',

            'carousels.*.subtitle_en.required' => 'The English subtitle is required.',
            'carousels.*.subtitle_en.string' => 'The English subtitle must be a text.',
            'carousels.*.subtitle_en.max' => 'The English subtitle may not be longer than 255 characters.',

            'carousels.*.subtitle_ar.required' => 'The Arabic subtitle is required.',
            'carousels.*.subtitle_ar.string' => 'The Arabic subtitle must be a text.',
            'carousels.*.subtitle_ar.max' => 'The Arabic subtitle may not be longer than 255 characters.',

            'carousels.*.product_id.required' => 'Please select a product.',
            'carousels.*.product_id.integer' => 'The product ID must be a valid number.',
            'carousels.*.product_id.exists' => 'The selected product does not exist.',

            'carousels.*.image.image' => 'The website image must be an image file.',
            'carousels.*.image.mimes' => 'The website image must be a file of type: jpeg, png, jpg, svg.',

            'carousels.*.mobile_image.image' => 'The mobile image must be an image file.',
            'carousels.*.mobile_image.mimes' => 'The mobile image must be a file of type: jpeg, png, jpg, svg.',
        ];
    }



}
