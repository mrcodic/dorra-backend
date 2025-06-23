<?php

namespace App\Http\Requests\Location;

use App\Http\Requests\Base\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends BaseRequest
{
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'state_id' => ['required', 'exists:states,id'],
            'address_line' => ['required', 'string', 'max:255'],
            'link' => ['required', 'string', 'max:255'],
            'days' => ['required', 'array', 'min:1'],
            'days.*' => ['integer', 'min:1', 'max:7'],
            'available_time' => ['required', 'regex:/^\d{2}:\d{2}\s?-\s?\d{2}:\d{2}$/'],
        ];
    }
}
