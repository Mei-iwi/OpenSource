<?php

namespace App\Support;

class CsvCell
{
    public static function safe(mixed $value): string
    {
        $text = (string) $value;

        // Spreadsheet applications may ignore leading whitespace or invisible characters.
        return preg_match('/^[\s\x00-\x1F\x7F\x{FEFF}\x{200B}\x{2060}]*[=+@-]/u', $text) ? "'".$text : $text;
    }
}
