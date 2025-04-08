<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\BaseRequest;
use App\Models\CountryCode;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;

class UpdateCategoryRequest extends BaseRequest
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
            'email' => ['sometimes', 'email', 'unique:users,email'],
            'phone_number' => ['sometimes', 'string', 'min:10', 'max:15', 'unique:users,phone_number', new Phone($isoCode),],
            'password' => ['sometimes', 'string', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'country_code_id' => ['sometimes', 'exists:country_codes,id'],
            'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,svg'],];
    }


}
