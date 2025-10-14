<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Base\BaseRequest;
use Illuminate\Validation\Rule;


class UpdateInventoryRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules($id): array
    {
        return [
            'name' => ['required',
                Rule::unique('inventories', 'name')->ignore($id, 'id'),
                'string', 'max:255'],
            'number' => ['required', 'integer'],
        ];
    }



}
