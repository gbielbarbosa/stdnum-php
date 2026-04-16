<?php

namespace StdNum\Countries\AD;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class NRT implements DocumentInterface
{
    use Cleanable;

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $compact = $this->compact($number);
        if (strlen($compact) === 8) {
            return $compact[0] . '-' . substr($compact, 1, 6) . '-' . $compact[7];
        }
        return $number;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-', '.'], '', $number)));
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 8) {
            return ValidationResult::failure('Invalid length for NRT');
        }

        if (!ctype_alpha($cleaned[0]) || !ctype_alpha($cleaned[7])) {
            return ValidationResult::failure('Invalid format for NRT');
        }

        $digits = substr($cleaned, 1, 6);
        if (!ctype_digit($digits)) {
            return ValidationResult::failure('Invalid format for NRT');
        }

        $firstLetter = $cleaned[0];
        if (!in_array($firstLetter, ['A','C','D','E','F','G','L','O','P','U'])) {
            return ValidationResult::failure('Invalid component for NRT');
        }

        if ($firstLetter === 'F' && $digits > '699999') {
            return ValidationResult::failure('Invalid component for NRT');
        }

        if (in_array($firstLetter, ['A', 'L']) && !($digits > '699999' && $digits < '800000')) {
            return ValidationResult::failure('Invalid component for NRT');
        }

        return ValidationResult::success();
    }
}
