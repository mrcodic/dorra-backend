<?php

namespace App\Http\Requests\SubIndustry;

use App\Http\Requests\Base\BaseRequest;
use App\Models\CountryCode;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;

class UpdateSubIndustryRequest extends BaseRequest
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
                'required',
                'string',
                'max:255',
//                Rule::unique('industries', 'name->en'),
            ],
            'name.ar' => [
                'nullable',
                'string',
                'max:255',
//                Rule::unique('industries', 'name->ar'),
            ],

            'parent_id' => ['required', 'integer', 'exists:industries,id'],
        ];

    }


}
