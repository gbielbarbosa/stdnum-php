<?php
namespace StdNum\Countries\ME;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class PIB implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $weights = [8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 7; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        
        $sum = -$sum;
        $check = $sum % 11;
        if ($check < 0) {
            $check += 11;
        }
        return (string)($check % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 8) {
            return ValidationResult::failure('Invalid length for PIB');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for PIB');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, 7)) !== $cleaned[7]) {
            return ValidationResult::failure('Invalid checksum for PIB');
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
        return trim(strtoupper(str_replace([' '], '', $number)));
    }
}
