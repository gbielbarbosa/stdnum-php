<?php

namespace StdNum\Countries\FR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\LuhnChecksum;

class SIREN implements DocumentInterface
{
    use LuhnChecksum;

    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 9) {
            return ValidationResult::failure('Invalid length for SIREN number.');
        }

        if (!ctype_digit($compact)) {
            return ValidationResult::failure('SIREN must contain only digits.');
        }

        if (!$this->verifyLuhn($compact)) {
            return ValidationResult::failure('Invalid checksum for SIREN number.');
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

        return substr($compact, 0, 3) . ' ' . substr($compact, 3, 3) . ' ' . substr($compact, 6);
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-', '.'], '', $number)));
    }
}

