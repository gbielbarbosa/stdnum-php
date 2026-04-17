<?php
namespace StdNum\Countries\PY;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class RUC implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $sum = 0;
        $rev = strrev($number);
        for ($i = 0; $i < strlen($rev); $i++) {
            $sum += ($i + 2) * (int)$rev[$i];
        }
        
        $s = -$sum % 11;
        if ($s < 0) {
            $s += 11;
        }
        
        return (string)($s % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) > 9) {
            return ValidationResult::failure('Invalid length for RUC');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for RUC');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, -1)) !== substr($cleaned, -1)) {
            return ValidationResult::failure('Invalid checksum for RUC');
        }

        return ValidationResult::success();
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $cleaned = $this->compact($number);
        if (strlen($cleaned) >= 2) {
            return substr($cleaned, 0, -1) . '-' . substr($cleaned, -1);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}
