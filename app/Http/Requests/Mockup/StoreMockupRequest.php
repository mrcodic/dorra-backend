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
        $types = request('types', []);
        $approach = request('approach') ?? request('q');
        $isWithout = $approach === 'without_editor' || $approach === 'without';
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
//            'approach' => [
//                'required',
//                'string',
//                'in:with_editor,without_editor',
//            ],
            'types.*' => ['required', Rule::in(TypeEnum::values())],
            'category_id' => ['required', 'integer', Rule::exists(Category::class, 'id')],
            'colors' => ['sometimes', 'array'],
            'templates' => ['nullable', 'array'],
            'templates.*.template_id' => ['nullable', 'exists:templates,id'],
            'templates.*.front_x' => ['nullable', 'numeric', 'min:0'],
            'templates.*.front_y' => ['nullable', 'numeric', 'min:0'],
            'templates.*.front_width' => ['nullable', 'numeric', 'min:0'],
            'templates.*.front_height' => ['nullable', 'numeric', 'min:0'],
            'templates.*.front_angle' => ['nullable', 'numeric'],

            'templates.*.back_x' => ['nullable', 'numeric', 'min:0'],
            'templates.*.back_y' => ['nullable', 'numeric', 'min:0'],
            'templates.*.back_width' => ['nullable', 'numeric', 'min:0'],
            'templates.*.back_height' => ['nullable', 'numeric', 'min:0'],
            'templates.*.back_angle' => ['nullable', 'numeric'],

            'templates.*.none_x' => ['nullable', 'numeric', 'min:0'],
            'templates.*.none_y' => ['nullable', 'numeric', 'min:0'],
            'templates.*.none_width' => ['nullable', 'numeric', 'min:0'],
            'templates.*.none_height' => ['nullable', 'numeric', 'min:0'],
            'templates.*.none_angle' => ['nullable', 'numeric'],

            'templates.*.colors' => [
                'required',
                'array',
            ],
            'front_base_image_id' => [
                Rule::requiredIf(in_array(1, $types)),
                'exists:media,id'
            ],
            'front_mask_image_id' => [
                Rule::requiredIf(in_array(1, $types)),
                'exists:media,id'
            ],
            'front_shadow_image_id' => [
                Rule::requiredIf(in_array(1, $types)),
                'exists:media,id'
            ],

            'back_base_image_id' => [
                Rule::requiredIf(in_array(2, $types)),
                'exists:media,id'

            ],
            'back_mask_image_id' => [
                Rule::requiredIf(in_array(2, $types)),
                'exists:media,id'
            ],
            'back_shadow_image_id' => [
                Rule::requiredIf(in_array(2, $types)),
                'exists:media,id'
            ],

            'none_base_image_id' => [
                Rule::requiredIf(in_array(3, $types)),
                'exists:media,id'
            ],
            'none_mask_image_id' => [
                Rule::requiredIf(in_array(3, $types)),
                'exists:media,id'
            ],
            'none_shadow_image_id' => [
                Rule::requiredIf(in_array(3, $types)),
                'exists:media,id'
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

    public function attributes()
    {
        return [
            'templates' => 'positions',
            'category_id' => 'product_id'
        ];
    }

}
