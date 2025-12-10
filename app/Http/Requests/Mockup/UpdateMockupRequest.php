<?php

namespace App\Http\Requests\Mockup;

use App\Enums\Mockup\TypeEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\Category;
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
            'category_id' => ['required','integer', Rule::exists(Category::class, 'id')],
            'colors' => ['sometimes','array'],
            'templates' => ['sometimes','array'],
            'templates.*.template_id' => ['sometimes','exists:templates,id'],
            'templates.*.front_x'      => ['nullable', 'numeric', 'min:0'],
            'templates.*.front_y'      => ['nullable', 'numeric', 'min:0'],
            'templates.*.front_width'  => ['nullable', 'numeric', 'min:0'],
            'templates.*.front_height' => ['nullable', 'numeric', 'min:0'],
            'templates.*.front_angle'  => ['nullable', 'numeric'],

            'templates.*.back_x'       => ['nullable', 'numeric', 'min:0'],
            'templates.*.back_y'       => ['nullable', 'numeric', 'min:0'],
            'templates.*.back_width'   => ['nullable', 'numeric', 'min:0'],
            'templates.*.back_height'  => ['nullable', 'numeric', 'min:0'],
            'templates.*.back_angle'   => ['nullable', 'numeric'],

            'templates.*.none_x'       => ['nullable', 'numeric', 'min:0'],
            'templates.*.none_y'       => ['nullable', 'numeric', 'min:0'],
            'templates.*.none_width'   => ['nullable', 'numeric', 'min:0'],
            'templates.*.none_height'  => ['nullable', 'numeric', 'min:0'],
            'templates.*.none_angle'   => ['nullable', 'numeric'],
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

    public function attributes()
    {
        return [
            'templates' => 'positions'
        ];
    }

}
