<?php

namespace App\Http\Requests\Flag;

use App\Http\Requests\Base\BaseRequest;
use App\Models\CountryCode;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;

class UpdateFlagRequest extends BaseRequest
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
            ],
            'name.ar' => [
                'required',
                'string',
                'max:255',
            ],
            'templates' => ['required', 'array'],
            'templates.*' => ['required', 'exists:templates,id'],
            'products' => ['required', 'array'],
            'products.*' => ['required', 'exists:products,id'],
        ];
    }


}
