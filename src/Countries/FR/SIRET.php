<?php

namespace StdNum\Countries\FR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\LuhnChecksum;

class SIRET implements DocumentInterface
{
    use LuhnChecksum;

    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 14) {
            return ValidationResult::failure('Invalid length for SIRET number.');
        }

        if (!ctype_digit($compact)) {
            return ValidationResult::failure('SIRET must contain only digits.');
        }

        if (!$this->verifyLuhn($compact)) {
            return ValidationResult::failure('Invalid checksum for SIRET number.');
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
        if (strlen($compact) !== 14) {
            return $number;
        }

        return substr($compact, 0, 3) . ' ' . substr($compact, 3, 3) . ' ' . substr($compact, 6, 3) . ' ' . substr($compact, 9);
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-', '.'], '', $number)));
    }
}

