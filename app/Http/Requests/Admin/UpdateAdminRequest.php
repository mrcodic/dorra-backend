<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;
use App\Models\CountryCode;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;

class UpdateAdminRequest extends BaseRequest
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
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('admins', 'email')],
            'phone_number' => ['sometimes', 'string', 'min:10', 'max:15', 'unique:admins,phone_number', new Phone($isoCode),],
            'password' => ['sometimes', 'string', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'country_code_id' => ['sometimes', 'exists:country_codes,id'],
            'status' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg'],];
    }


}
