<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Base\BaseRequest;
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
            'phone_number' => ['required', 'string', 'unique:users,phone_number',],
            'full_phone_number' => ['nullable', 'string', new Phone($isoCode)],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'country_code_id' => ['required', 'exists:country_codes,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg'],
            'addresses' => ['required', 'array', 'min:1'],
            'addresses.*.label' => ['required', 'string', 'min:3'],
            'addresses.*.line' => ['required', 'string', 'min:3'],
            'addresses.*.state_id' => ['required', 'integer', 'exists:states,id'],];


    }

    public function messages(): array
    {
        return [
            'addresses.*.label.required' => 'Each address must have a label (e.g., Home, Work).',
            'addresses.*.label.min' => 'Address label must be at least :min characters.',
            'addresses.*.line.required' => 'Each address must have a street or line value.',
            'addresses.*.state_id.required' => 'Each address must have a valid state selected.',
            'addresses.*.state_id.exists' => 'The selected state is not valid.',
        ];
    }





}
