<?php

namespace App\Support;

class SpreadsheetValueSanitizer
{
    public static function sanitize(mixed $value): string
    {
        $value = (string) $value;
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value) ?? '';

        if (preg_match('/^[=+\-@\t\r\n]/', $value) === 1) {
            $value = "'".$value;
        }

        return mb_substr($value, 0, 32767);
    }
}
