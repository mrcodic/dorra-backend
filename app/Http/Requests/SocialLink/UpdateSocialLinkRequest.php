<?php

namespace App\Http\Requests\SocialLink;

use App\Http\Requests\Base\BaseRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSocialLinkRequest extends BaseRequest
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
            'socials' => 'nullable|array',
            'socials.*.id' => ['nullable', 'integer', 'exists:social_links,id'],
            'socials.*.platform' => ['nullable', 'string', 'max:255'],
            'socials.*.url' => ['nullable', 'max:255', 'url:http,https', 'distinct'],

        ];
    }


    public function messages(): array
    {
        return [
            'socials.*.url' => 'Enter a valid URL (e.g. https://facebook.com/yourpage).',
        ];
    }

}
