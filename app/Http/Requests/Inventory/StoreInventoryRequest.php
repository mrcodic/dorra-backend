<?php

namespace App\Http\Requests\Inventory;

use App\Enums\DiscountCode\ScopeEnum;
use App\Enums\DiscountCode\TypeEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\Design;

class StoreInventoryRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required',
                'unique:inventories,name',
                'string', 'max:255'],
            'number' => ['required', 'integer'],
        ];


    }





}
