<?php

namespace StdNum\Countries\AU;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class TFN implements DocumentInterface
{
    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);
        $len = strlen($compact);

        if ($len !== 8 && $len !== 9) {
            return ValidationResult::failure('Invalid length for TFN.');
        }

        if (!ctype_digit($compact)) {
            return ValidationResult::failure('TFN must contain only digits.');
        }

        $weights = [1, 4, 3, 7, 5, 8, 6, 9, 10];
        $sum = 0;
        for ($i = 0; $i < $len; $i++) {
            $sum += (int)$compact[$i] * $weights[$i];
        }

        if ($sum % 11 !== 0) {
            return ValidationResult::failure('Invalid checksum for TFN.');
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
        if (strlen($compact) === 8 || strlen($compact) === 9) {
            return substr($compact, 0, 3) . ' ' . substr($compact, 3, 3) . ' ' . substr($compact, 6);
        }

        return $compact;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}
