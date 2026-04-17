<?php
namespace StdNum\Countries\RO;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class CUI implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $weights = [7, 5, 3, 2, 1, 7, 5, 3, 2];
        $number = str_pad($number, 9, '0', STR_PAD_LEFT);
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        $check = 10 * $sum;
        return (string)(($check % 11) % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned) || str_starts_with($cleaned, '0')) {
            return ValidationResult::failure('Invalid format for CUI');
        }

        $len = strlen($cleaned);
        if ($len < 2 || $len > 10) {
            return ValidationResult::failure('Invalid length for CUI');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, -1)) !== substr($cleaned, -1)) {
            return ValidationResult::failure('Invalid checksum for CUI');
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
        $number = trim(strtoupper(str_replace([' ', '-'], '', $number)));
        if (str_starts_with($number, 'RO')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
