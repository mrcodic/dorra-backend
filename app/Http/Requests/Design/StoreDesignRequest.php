<?php

namespace App\Http\Requests\Design;

use App\Http\Requests\Base\BaseRequest;


class StoreDesignRequest extends BaseRequest
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
            'template_id' => ['required', 'exists:templates,id'],
            'user_id' => ['required', 'exists:users,id'],
        ];

    }


}
