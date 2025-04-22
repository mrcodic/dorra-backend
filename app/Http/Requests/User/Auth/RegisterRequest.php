<?php

namespace App\Http\Requests\User\Auth;

use App\Http\Requests\Base\BaseRequest;
use App\Models\CountryCode;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;

class RegisterRequest extends BaseRequest
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
        $isoCode = CountryCode::find($this->country_code_id)?->iso_code ?? 'US';
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed',Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'otp' =>['required','numeric','digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered.',
            'phone_number.unique' => 'This phone number is already in use.',
            'password.confirmed' => 'Passwords do not match.',
            'country_code_id.exists' => 'Selected country is invalid.',
            'phone_number.required' => 'The phone number is required.',
            'phone_number.min' => 'The phone number must be at least 10 characters.',
            'phone_number.max' => 'The phone number must be at most 15 characters.',
            'phone_number.phone' => 'The phone number is not valid. Please provide a valid phone number, including the country code (e.g., +201234567890 for Egypt).',
        ];
    }

}
