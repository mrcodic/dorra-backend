<?php

namespace App\Http\Requests\Template;

use App\Enums\BorderEnum;
use App\Enums\OrientationEnum;
use App\Enums\SafetyAreaEnum;
use App\Enums\Template\StatusEnum;
use App\Enums\Template\TypeEnum;
use App\Http\Requests\Base\BaseRequest;

class UpdateTemplateRequest extends BaseRequest
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
            'name.en' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'name.ar' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'description.en' => [
                'nullable',
                'string',
            ],
            'description.ar' => [
                'nullable',
                'string',
            ],
            'type' => ['sometimes','in:'.TypeEnum::getValuesAsString()],
            'status' => ["required","integer","in:".StatusEnum::getValuesAsString()],
            'product_ids' => ['nullable','required_with:product_with_category', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'template_image_id' => ['required','exists:media,id'],

            'design_data' => ['sometimes', 'json'],
            'base64_preview_image' => ['sometimes', 'string'],
            'specifications' => ['sometimes', 'array'],
            'specifications.*' => ['sometimes', 'integer', 'exists:product_specifications,id'],
            'source_design_svg' => ['nullable', 'file', 'mimetypes:image/svg+xml', 'max:2048'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['exists:tags,id'],
            'flags' => ['sometimes', 'array'],
            'flags.*' => ['integer', 'exists:flags,id'],
            'types' => ['required', 'array'],
            'types.*' => ['integer', 'exists:types,id'],
            'orientation' => ['required', 'in:' . OrientationEnum::getValuesAsString()],
            'dimension_id' => ['nullable', 'integer', 'exists:dimensions,id'],
            'go_to_editor' => ['sometimes', 'boolean'],
            'has_corner' => ['required', 'in:0,1'],
            'has_safety_area' => ['sometimes', 'in:0,1'],
            'border' => ['sometimes', 'in:' . BorderEnum::getValuesAsString()],
            'safety_area' => ['sometimes', 'in:' . SafetyAreaEnum::getValuesAsString()],

        ];
    }


}
