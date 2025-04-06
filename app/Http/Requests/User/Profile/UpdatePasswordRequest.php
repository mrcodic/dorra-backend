<?php

namespace App\Http\Requests\User\Profile;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends BaseRequest
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
            'current_password' => ['required','current_password'],
            'password' => ['sometimes', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],

        ];
    }

    public function messages(): array
    {
        return [

        ];
    }

}
