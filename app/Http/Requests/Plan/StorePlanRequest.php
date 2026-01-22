<?php

namespace App\Http\Requests\Plan;

use App\Http\Requests\Base\BaseRequest;

class StorePlanRequest extends BaseRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'credits' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ];

    }
}
