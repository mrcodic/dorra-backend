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
            'country_id' => ['required', 'exists:countries,id'],
            'state_id' => ['required', 'exists:states,id'],
            'address_line' => ['required', 'string', 'max:255'],
            'link' => [
                'required',
                'string',
                'max:2048',
                'url',
                'link' => [
                    'required', 'string', 'max:2048', 'url',
                    'regex:~^https?://(?:(?:www\.)?google\.[^/]+/maps(?:[/?#]|$)|maps\.google\.[^/]+/|goo\.gl/maps/|maps\.app\.goo\.gl/|g\.page/)~i',
                ],

            ],

            'days' => ['required', 'array', 'min:1'],
            'days.*' => ['string'],
            'available_time' => ['required', 'regex:/^\d{2}:\d{2}\s?-\s?\d{2}:\d{2}$/'],
            'edit_start_time' =>['sometimes','date_format:H:i','before:edit_end_time'],
            'edit_end_time' =>['sometimes','date_format:H:i','after:edit_start_time'],
        ];

    }
}
