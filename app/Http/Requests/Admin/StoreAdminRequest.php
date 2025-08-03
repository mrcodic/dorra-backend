<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Base\BaseRequest;
use App\Models\CountryCode;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;

class StoreAdminRequest extends BaseRequest
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
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:admins,email'],
            'phone_number' => ['required', 'string','digits:11',],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'status' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],];

    }


}
