<?php

namespace App\Http\Requests\DiscountCode;

use App\Enums\DiscountCode\ScopeEnum;
use App\Enums\DiscountCode\TypeEnum;
use App\Http\Requests\Base\BaseRequest;

use Illuminate\Validation\Rule;

class UpdateDiscountCodeRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'max_usage' => ['nullable', 'integer', 'min:1'],
            'expired_at' => ['nullable', 'date', 'after:today'],
        ];
    }

}
