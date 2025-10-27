<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TimeRangeOrder implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !preg_match('/^\s*(\d{2}):(\d{2})\s*-\s*(\d{2}):(\d{2})\s*$/', $value, $m)) {
            $fail('صيغة الوقت غير صحيحة.');
            return;
        }
        [$all, $h1, $i1, $h2, $i2] = $m;
        $s = ((int)$h1) * 60 + (int)$i1;
        $e = ((int)$h2) * 60 + (int)$i2;

        // تحقّق نطاق الساعات والدقائق
        if ($h1 > 23 || $h2 > 23 || $i1 > 59 || $i2 > 59) {
            $fail('قيم الوقت غير صالحة.');
            return;
        }

        // ارفض الفاصل الليلي (إن أردت السماح به عدّل الشرط)
        if ($s >= $e) {
            $fail('وقت البداية يجب أن يكون قبل وقت النهاية.');
        }
    }
}
