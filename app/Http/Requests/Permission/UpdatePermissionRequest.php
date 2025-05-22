<?php

namespace App\Http\Requests\Permission;

use App\Http\Requests\Base\BaseRequest;
use App\Models\CountryCode;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;

class UpdatePermissionRequest extends BaseRequest
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
            'group.en' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('permissions', 'group->en')->ignore($id),
            ],
            'group.ar' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('permissions', 'group->ar')->ignore($id),
            ],

        ];
    }


}
