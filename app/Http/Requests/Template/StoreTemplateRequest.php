<?php

namespace App\Http\Requests\Template;

use App\Enums\Template\TypeEnum;
use App\Enums\Template\UnitEnum;
use App\Http\Requests\Base\BaseRequest;
use Illuminate\Validation\Rule;


class StoreTemplateRequest extends BaseRequest
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
            'name.en' => [
                'nullable',
                'string',
                'max:255',
            ],
            'name' => [
                'nullable',
//                'string',
                'max:255',
            ],
            'name.ar' => [
                'nullable',
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
            'product_id' => ['required', 'exists:products,id'],
            'design_data' => ['sometimes', 'json'],
            'base64_preview_image' => ['sometimes', 'string'],
            'height' => ["sometimes"],
            'width' => ["sometimes"],
            'unit' => ["sometimes","integer","in:".UnitEnum::getValuesAsString()],
            'specifications' => ['sometimes', 'array'],
            'specifications.*' => ['sometimes', 'integer', 'exists:product_specifications,id'],
//            'source_design_svg' => ['nullable', 'file', 'mimetypes:image/svg+xml', 'max:2048'],
            'source_design_svg' => ['nullable'],
        ];

    }


}
