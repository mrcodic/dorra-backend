<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Base\BaseRequest;


class UpdateInventoryRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'number' => ['required', 'integer'],
        ];
    }



}
