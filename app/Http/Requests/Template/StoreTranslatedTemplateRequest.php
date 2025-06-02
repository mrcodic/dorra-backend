<?php

namespace App\Http\Requests\Template;

use App\Enums\Template\TypeEnum;
use App\Enums\Template\UnitEnum;
use App\Http\Requests\Base\BaseRequest;
use Illuminate\Validation\Rule;


class StoreTranslatedTemplateRequest extends BaseRequest
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
                'required',
                'string',
                'max:255',
                Rule::unique('templates', 'name->en'),
            ],
            'name.ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('templates', 'name->ar'),
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
            'height' => ["required","numeric"],
            'width' => ["required","numeric"],
            'unit' => ["required","integer","in:".UnitEnum::getValuesAsString()],
            'product_id' => ['required', 'exists:products,id'],
            'design_data' => ['sometimes', 'json'],
            'base64_preview_image' => ['sometimes', 'string'],
            'specifications' => ['sometimes', 'array'],
            'specifications.*' => ['sometimes', 'integer', 'exists:product_specifications,id'],
            'source_design_svg' => ['nullable'],
//            'source_design_svg' => ['nullable', 'file', 'mimetypes:image/svg+xml', 'max:2048'],
        ];

    }


}
