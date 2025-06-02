<?php

namespace App\Http\Requests\Template;

use App\Enums\Template\StatusEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\CountryCode;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;

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
                Rule::unique('templates', 'name->en')->ignore($id),
            ],
            'name.ar' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('templates', 'name->ar')->ignore($id),
            ],
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ["sometimes","integer","in:".StatusEnum::getValuesAsString()],
            'product_id' => ['sometimes', 'exists:products,id'],
            'design_data' => ['sometimes', 'json'],
            'base64_preview_image' => ['sometimes', 'string'],
            'specifications' => ['sometimes', 'array'],
            'specifications.*' => ['sometimes', 'integer', 'exists:product_specifications,id'],
            'source_design_svg' => ['nullable', 'file', 'mimetypes:image/svg+xml', 'max:2048'],
        ];
    }


}
