<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DimensionWithinUnitRange implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $unit = request('unit');

        switch ($unit) {
            case 2:
                if ($value < 1.32 || $value > 211.66) {
                    $fail(__(":attribute must be between 1.32 cm and 211.66 cm."));
                }
                break;

            case 1:
                if ($value < 50 || $value > 8000) {
                    $fail(__(":attribute must be between 50px and 8000px."));
                }
                break;

            default:
                $fail(__("A valid unit (cm, px) must be specified."));
                break;
        }
    }
}
