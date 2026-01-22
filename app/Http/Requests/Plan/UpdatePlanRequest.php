<?php

namespace App\Http\Requests\Plan;

use App\Http\Requests\Base\BaseRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdatePlanRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules($id): array
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
