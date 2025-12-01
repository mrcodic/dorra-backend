<?php

namespace App\Http\Requests\Mockup;

use App\Enums\Mockup\TypeEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Validation\Rule;

class StoreMockupRequest extends BaseRequest
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


    public function rules()
    {
        $types = $this->input('types', []);

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'types.*' => ['required', Rule::in(TypeEnum::values())],
            'category_id' => ['required','integer', Rule::exists(Category::class, 'id')],
            'colors' => ['required','array'],
            'templates' => ['required','array'],
            'templates.*.template_id' => ['required','exists:templates,id','distinct'],
            'templates.*.front' => ['sometimes', 'exists:positions,id'],
            'templates.*.back' => ['sometimes', 'exists:positions,id'],
            'templates.*.none' => ['sometimes', 'exists:positions,id'],
            'front_base_image' => [
                Rule::requiredIf(in_array(1, $types)),
                'image',
//                'mimes:jpg',
            ],
            'front_mask_image' => [
                Rule::requiredIf(in_array(1, $types)),
                'image',
//                'mimes:png',
            ],

            'back_base_image' => [
                Rule::requiredIf(in_array(2, $types)),
                'image',
//                'mimes:jpg',
            ],
            'back_mask_image' => [
                Rule::requiredIf(in_array(2, $types)),
                'image',
//                'mimes:png',
            ],

            'none_base_image' => [
                Rule::requiredIf(in_array(3, $types)),
                'image',
//                'mimes:jpg',
            ],
            'none_mask_image' => [
                Rule::requiredIf(in_array(3, $types)),
                'image',
//                'mimes:png',
            ],
        ];
    }

    public function messages()
    {
        return [
            'image.mimes' => 'The image must be a PNG file.',
            'image.required' => 'Please upload an image.',
            'image.image' => 'The file must be an image.',
        ];
    }

}
