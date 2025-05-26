<?php

namespace App\Http\Requests\Template;

use App\Http\Requests\Base\BaseRequest;


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
            'name' => ['required', 'string', 'max:255','unique:templates,name'],
            'product_id' => ['required', 'exists:products,id'],
            'design_data' => ['required', 'json'],
            'preview_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'source_design_svg' => ['nullable', 'file', 'mimetypes:image/svg+xml', 'max:2048'],
        ];

    }


}
