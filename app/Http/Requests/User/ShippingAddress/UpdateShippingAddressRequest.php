<?php

namespace App\Http\Requests\User\ShippingAddress;

use App\Http\Requests\BaseRequest;


class UpdateShippingAddressRequest extends BaseRequest
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
            'user_id' => $this->user()?->id,
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
            'label' => ['sometimes', 'string', 'max:255'],
            'line' => ['sometimes', 'string', 'max:500'],
            'state_id' => ['sometimes', 'exists:states,id'],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }
}
