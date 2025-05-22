<?php

namespace App\Http\Requests\Permission;

use App\Http\Requests\Base\BaseRequest;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends BaseRequest
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
            'group.en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'group->en'),
            ],
            'group.ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'group->ar'),
            ],

        ];

    }

}
