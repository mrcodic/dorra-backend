<?php

namespace App\Http\Requests\Base;

use App\Enums\HttpEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Response;

class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            Response::api(
                HttpEnum::UNPROCESSABLE_ENTITY,
                message: 'Validation error',
                errors: $validator->errors()
            )
        );
    }
}
