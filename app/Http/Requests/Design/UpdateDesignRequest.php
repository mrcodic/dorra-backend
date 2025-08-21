<?php

namespace App\Http\Requests\Design;

use App\Http\Requests\Base\BaseRequest;
use App\Models\CountryCode;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;

class UpdateDesignRequest extends BaseRequest
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
            'design_data' => ['sometimes', 'json'],
            'design_back_data' => ['sometimes', 'json'],
            'base64_preview_image' => ['sometimes', 'string', 'required_without:design_image'],
            'back_base64_preview_image' => ['sometimes', 'string'],
            'design_image' => ['sometimes', 'file', 'mimetypes:image/svg+xml', 'max:2048', 'required_without:base64_preview_image'],
            'name' => ['sometimes','string','max:255'],
            'description' => ['sometimes','string','max:1000'],
        ];
    }


}
