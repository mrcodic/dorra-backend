<?php

namespace App\Http\Requests\Team;

use App\Http\Requests\Base\BaseRequest;
use Illuminate\Validation\Rule;

class StoreTeamRequest extends BaseRequest
{
    /**
     * Determine if the v1 is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    public function prepareForValidation()
    {
        $this->merge([
            'owner_id' =>auth('sanctum')->id(),
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
            'name' => ['required', 'string', 'max:255',],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
            'emails' => ['nullable', 'array'],
            'emails.*' => ['nullable', 'email','exists:users,email'],

        ];
    }



}
