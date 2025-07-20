<?php

namespace App\Http\Requests\Dimension;

use App\Enums\DiscountCode\ScopeEnum;
use App\Enums\DiscountCode\TypeEnum;
use App\Enums\Product\UnitEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Rules\DimensionWithinUnitRange;

class StoreDimensionRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'height' => [
                'numeric',
                new DimensionWithinUnitRange()
            ],
            'width' => [
                'numeric',
                new DimensionWithinUnitRange()
            ],
            'unit' => [
                'integer',
                'in:' . UnitEnum::getValuesAsString(),
            ],
            'is_custom' => ['nullable', 'boolean'],
            'name' => ['nullable'],
        ];
    }

    public function passedValidation()
    {
        $this->merge([
            'is_custom' => 1,
            'name' => $this->width . '*' . $this->height
        ]);
    }

}
