<?php

namespace App\Http\Requests\Folder;

use App\Enums\DiscountCode\ScopeEnum;
use App\Enums\DiscountCode\TypeEnum;
use App\Http\Requests\Base\BaseRequest;

class StoreFolderRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable','string','max:1000'],
            'designs' => ['nullable', 'array'],
            'designs.*' => ['nullable', 'integer', 'exists:designs,id'],
        ];
    }


}
