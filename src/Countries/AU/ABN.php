<?php

namespace StdNum\Countries\AU;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class ABN implements DocumentInterface
{
    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 11) {
            return ValidationResult::failure('Invalid length for ABN.');
        }

        if (!ctype_digit($compact)) {
            return ValidationResult::failure('ABN must contain only digits.');
        }

        // Weights: 10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19
        $weights = [10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19];
        $sum = 0;
        
        $firstDigitProcessed = (int)$compact[0] - 1;
        $sum += $firstDigitProcessed * $weights[0];

        for ($i = 1; $i < 11; $i++) {
            $sum += (int)$compact[$i] * $weights[$i];
        }

        if ($sum % 89 !== 0) {
            return ValidationResult::failure('Invalid checksum for ABN.');
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

        return substr($compact, 0, 2) . ' ' . substr($compact, 2, 3) . ' ' . substr($compact, 5, 3) . ' ' . substr($compact, 8);
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}
