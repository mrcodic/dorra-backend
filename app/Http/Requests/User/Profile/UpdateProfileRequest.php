<?php

namespace App\Http\Requests\User\Profile;

use App\Http\Requests\Base\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends BaseRequest
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
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore(request()->user()->email,'email')],
            'phone_number' => ['sometimes', 'string', 'min:10', 'max:15',Rule::unique('users')->ignore(request()->user()->phone_number,'phone_number')],
            'country_code_id' => ['sometimes', 'exists:country_codes,id'],
            'image' => ['sometimes', 'image' , 'mimes:jpeg,png,jpg,svg'],
            'notification_types' => ['sometimes', 'array'],
            'notification_types.*' =>    ['sometimes', 'integer' ,'exists:notification_types,id'],
            'is_mobile_notifications_enabled' => ['sometimes', 'boolean'],
            'is_email_notifications_enabled' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.string' => 'First name must be a valid string.',
            'first_name.max' => 'First name cannot be longer than 255 characters.',

            'last_name.string' => 'Last name must be a valid string.',
            'last_name.max' => 'Last name cannot be longer than 255 characters.',

            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already in use.',

            'phone_number.string' => 'Phone number must be a valid string.',
            'phone_number.min' => 'Phone number must be at least 10 digits.',
            'phone_number.max' => 'Phone number cannot be more than 15 digits.',
            'phone_number.unique' => 'This phone number is already in use.',

            'country_code_id.exists' => 'Selected country code is not valid.',

            'image.image' => 'Uploaded file must be an image.',
            'image.mimes' => 'Image must be a file of type: jpeg, png, jpg, or svg.',

            'notification_types.array' => 'Notification types must be an array.',
            'notification_types.*.integer' => 'Each notification type must be a valid ID.',
            'notification_types.*.exists' => 'One or more selected notification types are invalid.',
        ];
    }

}
