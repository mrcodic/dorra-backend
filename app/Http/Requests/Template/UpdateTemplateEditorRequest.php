<?php

namespace App\Http\Requests\Template;

use App\Enums\Template\StatusEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\CountryCode;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;

class UpdateTemplateEditorRequest extends BaseRequest
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
                'sometimes',
                'string',
                'max:255',
            ],
            'name.ar' => [
                'sometimes',
                'string',
                'max:255',

            ],
            'design_data' => ['sometimes', 'json'],
            'design_back_data' => ['sometimes', 'json'],
            'base64_preview_image' => ['sometimes', 'string'],
            'back_base64_preview_image' => ['sometimes', 'string'],
            'source_design_svg' => ['nullable', 'file', 'mimetypes:image/svg+xml', 'max:2048'],

        ];
    }


}
