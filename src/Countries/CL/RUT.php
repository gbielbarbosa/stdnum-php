<?php
namespace StdNum\Countries\CL;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class RUT implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $weights = [2, 3, 4, 5, 6, 7];
        $sum = 0;
        $rev = strrev($number);
        for ($i = 0; $i < strlen($rev); $i++) {
            $sum += (int)$rev[$i] * $weights[$i % 6];
        }
        $remainder = $sum % 11;
        $check = 11 - $remainder;
        if ($check === 11) {
            return '0';
        }
        if ($check === 10) {
            return 'K';
        }
        return (string)$check;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 8 && strlen($cleaned) !== 9) {
            return ValidationResult::failure('Invalid length for RUT');
        }

        if (!ctype_digit(substr($cleaned, 0, -1))) {
            return ValidationResult::failure('Invalid format for RUT');
        }

        if ($cleaned[strlen($cleaned) - 1] !== $this->calcCheckDigit(substr($cleaned, 0, -1))) {
            return ValidationResult::failure('Invalid checksum for RUT');
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
        $number = trim(strtoupper(str_replace([' ', '-', '.'], '', $number)));
        if (str_starts_with($number, 'CL')) {
            return substr($number, 2);
        }
        return $number;
    }
}
