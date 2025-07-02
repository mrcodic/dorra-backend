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
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'type' => ['required', Rule::in(TypeEnum::values())],
            'product_id' => ['required','integer', Rule::exists(Product::class, 'id')],
            'colors' => ['sometimes','array'],
            'image' => ['sometimes', 'image', 'mimes:png,'],
        ];
    }


}
