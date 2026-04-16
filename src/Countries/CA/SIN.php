<?php

namespace StdNum\Countries\CA;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\LuhnChecksum;

class SIN implements DocumentInterface
{
    use LuhnChecksum;

    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 9) {
            return ValidationResult::failure('Invalid length for SIN.');
        }

        if (!ctype_digit($compact)) {
            return ValidationResult::failure('SIN must contain only digits.');
        }

        if (in_array($compact[0], ['0', '8'])) {
            return ValidationResult::failure('Invalid starting digit for SIN.');
        }

        if (!$this->verifyLuhn($compact)) {
            return ValidationResult::failure('Invalid checksum for SIN.');
        }

        return ValidationResult::success();
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $compact = $this->compact($number);
        if (strlen($compact) !== 9) {
            return $number;
        }

        return substr($compact, 0, 3) . '-' . substr($compact, 3, 3) . '-' . substr($compact, 6);
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}

