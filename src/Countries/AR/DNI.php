<?php

namespace StdNum\Countries\AR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class DNI implements DocumentInterface
{
    use Cleanable;

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $compact = $this->compact($number);
        if (strlen($compact) >= 7) {
            return number_format((float)$compact, 0, '', '.');
        }
        return $number;
    }

    public function compact(string $number): string
    {
        return trim(str_replace([' ', '.'], '', $number));
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for DNI');
        }

        if (strlen($cleaned) !== 7 && strlen($cleaned) !== 8) {
            return ValidationResult::failure('Invalid length for DNI');
        }

        return ValidationResult::success();
    }
}
