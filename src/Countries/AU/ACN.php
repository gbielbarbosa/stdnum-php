<?php

namespace StdNum\Countries\AU;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class ACN implements DocumentInterface
{
    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 9) {
            return ValidationResult::failure('Invalid length for ACN.');
        }

        if (!ctype_digit($compact)) {
            return ValidationResult::failure('ACN must contain only digits.');
        }

        if ($this->calcCheckDigit(substr($compact, 0, 8)) !== $compact[8]) {
            return ValidationResult::failure('Invalid checksum for ACN.');
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
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }

    protected function calcCheckDigit(string $base): string
    {
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += (int)$base[$i] * (8 - $i);
        }
        $rem = $sum % 10;
        $check = (10 - $rem) % 10;
        
        return (string)$check;
    }
}
