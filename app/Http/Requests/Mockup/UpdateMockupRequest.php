<?php

namespace App\Http\Requests\Mockup;

use App\Enums\Mockup\TypeEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\CountryCode;
use App\Models\Product;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;

class UpdateMockupRequest extends BaseRequest
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
        $types = $this->input('types', []);
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'types' => ['required','array'],
            'types.*' => ['required', Rule::in(TypeEnum::values())],
            'product_id' => ['required','integer', Rule::exists(Product::class, 'id')],
            'colors' => ['sometimes','array'],
            'front_mask_image' => [
                Rule::requiredIf(in_array(1, $types)),
                'image',
                'mimes:png',
            ],

            'back_base_image' => [
                Rule::requiredIf(in_array(2, $types)),
                'image',
                'mimes:jpg',
            ],
            'back_mask_image' => [
                Rule::requiredIf(in_array(2, $types)),
                'image',
                'mimes:png',
            ],

            'none_base_image' => [
                Rule::requiredIf(in_array(3, $types)),
                'image',
                'mimes:jpg',
            ],
            'none_mask_image' => [
                Rule::requiredIf(in_array(3, $types)),
                'image',
                'mimes:png',
            ],
        ];
    }


}
