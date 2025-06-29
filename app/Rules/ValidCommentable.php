<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ValidCommentable implements ValidationRule
{
    public function __construct(private string $typeField, private array $map)
    {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $commentableType = request($this->typeField);

        $table = $this->map[$commentableType] ?? null;

        if (!$table) {
            $fail("The selected type is invalid.");
            return;
        }

        $exists = DB::table($table)->where('id', $value)->exists();

        if (! $exists) {
            $fail("The selected $attribute does not exist in the $commentableType.");
        }
    }
}
