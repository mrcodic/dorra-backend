<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Base\BaseRequest;
use App\Models\CountryCode;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;

class UpdateUserRequest extends BaseRequest
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
     * @param int $id
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules($id): array
    {
        $isoCode = CountryCode::find($this->country_code_id)?->iso_code ?? 'US';
        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($id)],
            'phone_number' => ['sometimes', 'string', Rule::unique('users', 'phone_number')->ignore($id),],
            'full_phone_number' => ['nullable', 'string', new Phone($isoCode)],
            'status' => ['required', 'boolean'],
            'password' => ['sometimes', 'string', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'country_code_id' => ['sometimes', 'exists:country_codes,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg'],];
    }

    protected function prepareForValidation()
    {
        if ($this->has(['country_code_id', 'phone_number'])) {
            $countryCode = CountryCode::find($this->country_code_id);

            if ($countryCode) {
                $cleanedPhone = preg_replace('/\D+/', '', $this->phone_number);
                if ($cleanedPhone) {
                    $this->merge([
                        'full_phone_number' => '+' . $countryCode->phone_code . $cleanedPhone,
                    ]);
                }
            }
        }
    }


}
