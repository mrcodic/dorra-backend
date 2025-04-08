<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use App\Models\CountryCode;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;

class StoreUserRequest extends BaseRequest
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
            'phone_number' => ['required', 'string', 'min:10', 'max:15', 'unique:users,phone_number', new Phone($isoCode),],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'country_code_id' => ['required', 'exists:country_codes,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg'],];

    }


}
