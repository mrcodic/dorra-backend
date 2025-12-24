<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidFontFile implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $allowedExts = ['ttf', 'otf', 'woff', 'woff2', 'eot'];
        $allowedMimes = [
            'font/ttf',
            'font/otf',
            'font/woff',
            'font/woff2',
            'application/x-font-ttf',
            'application/x-font-otf',
            'application/x-font-woff',
            'application/font-sfnt',
            'application/vnd.ms-fontobject',
            'application/vnd.ms-opentype',
            'application/octet-stream',
        ];

        $ext = strtolower($value->getClientOriginalExtension());
        $mime = $value->getMimeType();
        if (!in_array($ext, $allowedExts) && !in_array($mime, $allowedMimes)) {
            $fail('The ' . $attribute . ' must be a valid font file (TTF, OTF, WOFF, WOFF2, EOT).');
        }
    }
}
