<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TimeRangeOrder implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            !is_string($value)
            || !preg_match('/^\s*(\d{2}):(\d{2})\s*-\s*(\d{2}):(\d{2})\s*$/', $value, $m)
        ) {
            $fail('Invalid time format. Expected format: HH:MM - HH:MM');
            return;
        }

        [$all, $h1, $i1, $h2, $i2] = $m;
        $s = ((int) $h1) * 60 + (int) $i1;
        $e = ((int) $h2) * 60 + (int) $i2;

        if ($h1 > 23 || $h2 > 23 || $i1 > 59 || $i2 > 59) {
            $fail('Invalid hour or minute values.');
            return;
        }

        if ($s >= $e) {
            $fail('Start time must be before end time.');
        }
    }
}
