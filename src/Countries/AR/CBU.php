<?php

namespace StdNum\Countries\AR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class CBU implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $weights = [3, 1, 7, 9];
        $sum = 0;
        
        $length = strlen($number);
        for ($i = 0; $i < $length; $i++) {
            // Processing backwards
            $digit = (int)$number[$length - 1 - $i];
            $sum += $digit * $weights[$i % 4];
        }
        
        return (string)((10 - ($sum % 10)) % 10);
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $compact = $this->compact($number);
        if (strlen($compact) === 22) {
            return substr($compact, 0, 8) . ' ' . substr($compact, 8);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        return trim(str_replace([' ', '-'], '', $number));
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 22) {
            return ValidationResult::failure('Invalid length for CBU');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for CBU');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, 7)) !== $cleaned[7]) {
            return ValidationResult::failure('Invalid checksum for CBU (block 1)');
        }

        if ($this->calcCheckDigit(substr($cleaned, 8, 13)) !== $cleaned[21]) {
            return ValidationResult::failure('Invalid checksum for CBU (block 2)');
        }

        return ValidationResult::success();
    }
}
