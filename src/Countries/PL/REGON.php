<?php
namespace StdNum\Countries\PL;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class REGON implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        if (strlen($number) === 8) {
            $weights = [8, 9, 2, 3, 4, 5, 6, 7];
        } else {
            $weights = [2, 4, 8, 5, 0, 9, 7, 3, 6, 1, 2, 4, 8];
        }
        
        $sum = 0;
        $len = count($weights);
        for ($i = 0; $i < $len; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        
        return (string)(($sum % 11) % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for REGON');
        }

        $len = strlen($cleaned);
        if ($len !== 9 && $len !== 14) {
            return ValidationResult::failure('Invalid length for REGON');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, -1)) !== substr($cleaned, -1)) {
            return ValidationResult::failure('Invalid checksum for REGON');
        }

        if ($len === 14) {
            if ($this->calcCheckDigit(substr($cleaned, 0, 8)) !== $cleaned[8]) {
                return ValidationResult::failure('Invalid checksum for REGON');
            }
        }

        return ValidationResult::success();
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        return $this->compact($number);
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}
