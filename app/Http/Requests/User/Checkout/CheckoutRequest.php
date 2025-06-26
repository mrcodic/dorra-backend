<?php

namespace App\Http\Requests\User\Checkout;

use App\Enums\Order\OrderTypeEnum;
use App\Models\CountryCode;
use Illuminate\Foundation\Http\FormRequest;
use Propaganistas\LaravelPhone\Rules\Phone;

class CheckoutRequest extends FormRequest
{
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
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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
            'payment_method_id' => 'required|exists:payment_methods,id',
            'discount_code_id' => 'required|exists:discount_codes,id',
            'country_code_id' => ['required', 'exists:country_codes,id'],
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'full_phone_number' => ['nullable', 'string', new Phone($isoCode)],
            'type' => ['required', 'in:'.OrderTypeEnum::getValuesAsString()],
            'shipping_address_id' => ['required', 'exists:shipping_addresses,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'pickup_contact_first_name' => ['required', 'string'],
            'pickup_contact_last_name' => ['required', 'string'],
            'pickup_contact_email' => ['required', 'email'],
            'pickup_contact_phone_number' => ['nullable', 'string', new Phone($isoCode)],

        ];
    }

}
