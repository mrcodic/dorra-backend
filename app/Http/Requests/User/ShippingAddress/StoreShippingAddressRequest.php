<?php

namespace App\Http\Requests\User\ShippingAddress;

use App\Http\Requests\Base\BaseRequest;
use App\Models\Guest;

class StoreShippingAddressRequest extends BaseRequest
{
    /**
     * Determine if the v1 is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    public function prepareForValidation(): void
    {
        $cookieValue = getCookie('cookie_id')['value'];
        $guestId = null;
        if ($cookieValue) {
            $guest = Guest::firstOrCreate(['cookie_value' => $cookieValue]);
            $guestId = $guest->id;
        }
        $this->merge([
            'user_id' => $this->user('sanctum')?->id,
            'guest_id' => $guestId
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string','min:4', 'max:255'],
            'line' => ['required', 'string','min:4', 'max:500'],
            'state_id' => ['required', 'exists:states,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'guest_id' => ['nullable', 'exists:guests,id'],
            'cookie_id' => ['nullable'],
            ];

    }


}
