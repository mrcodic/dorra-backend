<?php

namespace App\Http\Requests\CreditOrder;

use App\Enums\DiscountCode\ScopeEnum;
use App\Enums\DiscountCode\TypeEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\Design;
use App\Models\Plan;

class StoreCreditOrderRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => 'required|exists:plans,id',
            'user_id' => 'required|exists:users,id',
            'credits' => 'nullable|integer',
            'amount' => 'nullable|numeric',
        ];


    }




}
