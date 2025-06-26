<?php

namespace App\Http\Requests\User\Checkout;

use Illuminate\Validation\Rule;
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

        if (
            $this->type == OrderTypeEnum::PICKUP &&
            $this->has(['pickup_country_code_id', 'pickup_contact_phone_number'])
        ) {
            $countryCode = CountryCode::find($this->country_code_id);

            if ($countryCode) {
                $cleanedPickupPhone = preg_replace('/\D+/', '', $this->pickup_contact_phone_number);
                if ($cleanedPickupPhone) {
                    $this->merge([
                        'pickup_full_phone_number' => '+' . $countryCode->phone_code . $cleanedPickupPhone,
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
            'payment_method_id' => 'exists:payment_methods,id',
            'discount_code_id' => ['nullable', 'exists:discount_codes,id'],
            'country_code_id' => ['required', 'exists:country_codes,id'],
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'full_phone_number' => ['nullable', 'string', new Phone($isoCode)],
            'type' => ['required', 'in:' . OrderTypeEnum::getValuesAsString()],


            'shipping_address_id' => [
                Rule::requiredIf($this->type == OrderTypeEnum::SHIPPING->value),
                Rule::prohibitedIf($this->type == OrderTypeEnum::PICKUP->value),
                'exists:shipping_addresses,id',
            ],
            'location_id' => [
                Rule::requiredIf($this->type == OrderTypeEnum::PICKUP->value),
                Rule::prohibitedIf($this->type == OrderTypeEnum::SHIPPING->value),
                'exists:locations,id',
            ],

            'pickup_contact_country_code_id' => [
                Rule::requiredIf($this->type == OrderTypeEnum::PICKUP->value),
                'exists:country_codes,id'],
            'pickup_contact_first_name' => [
                Rule::requiredIf($this->type == OrderTypeEnum::PICKUP->value),
                'string',
            ],
            'pickup_contact_last_name' => [
                Rule::requiredIf($this->type == OrderTypeEnum::PICKUP->value),
                'string',
            ],
            'pickup_contact_email' => [
                Rule::requiredIf($this->type == OrderTypeEnum::PICKUP->value),
                'email',
            ],
            'pickup_full_phone_number' => [
                'nullable',
                'string',
                new Phone($isoCode),
            ],
            'pickup_contact_phone_number' => [Rule::requiredIf($this->type == OrderTypeEnum::PICKUP->value),]

        ];
    }


}
