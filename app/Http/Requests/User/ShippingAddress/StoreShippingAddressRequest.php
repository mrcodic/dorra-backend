<?php

namespace App\Http\Requests\User\ShippingAddress;

use App\Http\Requests\Base\BaseRequest;

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
        $this->merge([
            'user_id' => $this->user('sanctum')?->id,
            'cookie_id' => getCookie('cookie_id')['value']
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
            'label' => ['required', 'string', 'max:255'],
            'line' => ['required', 'string', 'max:500'],
            'state_id' => ['required', 'exists:states,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'cookie_id' => ['nullable'],
            ];

    }


}
