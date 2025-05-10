<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\Base\BaseRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends BaseRequest
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
                Rule::unique('categories', 'name->en'),
            ],
            'name.ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name->ar'),
            ],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,svg'],
        ];

    }

}
