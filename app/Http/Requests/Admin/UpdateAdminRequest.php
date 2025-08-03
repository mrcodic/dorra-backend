<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Base\BaseRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAdminRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules($id): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('admins', 'email')->ignore($id)],
            'phone_number' => ['required', 'string', 'min:10', 'max:15'],
            'status' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg'],
            'role_id' => ['sometimes', 'integer', 'exists:roles,id'],
            'password' => [
                'nullable',
                'string',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols()
            ],
        ];
    }
}
