<?php

namespace StdNum\Countries\GB;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class UTR implements DocumentInterface
{
    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (!ctype_digit($compact)) {
            return ValidationResult::failure('UTR must contain only digits.');
        }

        if (strlen($compact) !== 10) {
            return ValidationResult::failure('Invalid length for a UTR number.');
        }

        if ($compact[0] !== $this->calcCheckDigit(substr($compact, 1))) {
            return ValidationResult::failure('Invalid checksum for the UTR number.');
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
        if (strlen($compact) !== 10) {
            return $number;
        }

        return $compact; // UTR does not have a standard formatting other than 10 digits
    }

    public function compact(string $number): string
    {
        return ltrim(trim(strtoupper(str_replace(' ', '', $number))), 'K');
    }

    protected function calcCheckDigit(string $number): string
    {
        $weights = [6, 7, 8, 9, 10, 5, 4, 3, 2];
        $sum = 0;
        
        for ($i = 0; $i < 9; $i++) {
            $sum += (int)$number[$i] * $weights[$i];
        }

        $chars = '21987654321';
        return $chars[$sum % 11];
    }
}

