<?php

namespace StdNum\Countries\IT;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\LuhnChecksum;

class IVA implements DocumentInterface
{
    use LuhnChecksum;

    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 11) {
            return ValidationResult::failure('Invalid length for IVA number.');
        }

        if (!ctype_digit($compact)) {
            return ValidationResult::failure('IVA must contain only digits.');
        }

        if (!$this->verifyLuhn($compact)) {
            return ValidationResult::failure('Invalid checksum for IVA number.');
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
        if (strlen($compact) !== 11) {
            return $number;
        }
        return $compact; // IVA does not typically have standard punctuation
    }

    public function compact(string $number): string
    {
        $compact = trim(strtoupper(str_replace([' ', '-'], '', $number)));
        if (str_starts_with($compact, 'IT')) {
            $compact = substr($compact, 2);
        }
        return $compact;
    }
}

