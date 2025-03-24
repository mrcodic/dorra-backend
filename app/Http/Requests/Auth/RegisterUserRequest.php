<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'min:10', 'max:15', 'unique:users,phone_number'],
            'password' => ['required', 'string', 'confirmed',Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'otp' =>['required','numeric','digits:6'],
            'country_code_id' => ['required', 'exists:country_codes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered.',
            'phone_number.unique' => 'This phone number is already in use.',
            'password.confirmed' => 'Passwords do not match.',
            'country_code_id.exists' => 'Selected country is invalid.',
        ];
    }
}
