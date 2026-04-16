<?php
namespace StdNum\Countries\CO;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class NIT implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $weights = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
        $sum = 0;
        $rev = strrev($number);
        for ($i = 0; $i < strlen($rev); $i++) {
            $sum += $weights[$i] * (int)$rev[$i];
        }
        $s = $sum % 11;
        $map = '01987654321';
        return $map[$s];
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) < 8 || strlen($cleaned) > 16) {
            return ValidationResult::failure('Invalid length for NIT');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for NIT');
        }

        if ($cleaned[strlen($cleaned) - 1] !== $this->calcCheckDigit(substr($cleaned, 0, -1))) {
            return ValidationResult::failure('Invalid checksum for NIT');
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
        if (strlen($compact) >= 8) {
            $main = substr($compact, 0, -1);
            $check = substr($compact, -1);
            $formatted = number_format((float)$main, 0, '', '.');
            return $formatted . '-' . $check;
        }
        return $number;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-', '.', ','], '', $number)));
    }
}
