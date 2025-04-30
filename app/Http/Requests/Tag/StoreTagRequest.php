<?php

namespace App\Http\Requests\Tag;

use App\Http\Requests\Base\BaseRequest;
use App\Models\CountryCode;
use Illuminate\Validation\Rule;

class StoreTagRequest extends BaseRequest
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
                Rule::unique('tags', 'name->en'),
            ],
            'name.ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name->ar'),
            ],
        ];

    }


}
