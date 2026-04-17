<?php
namespace StdNum\Countries\MK;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class EDB implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $weights = [7, 6, 5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $total = 0;
        for ($i = 0; $i < 12; $i++) {
            $total += $weights[$i] * (int)$number[$i];
        }
        $check = (-$total % 11);
        if ($check < 0) {
            $check += 11;
        }
        return (string)($check % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 13) {
            return ValidationResult::failure('Invalid length for EDB');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for EDB');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, 12)) !== $cleaned[12]) {
            return ValidationResult::failure('Invalid checksum for EDB');
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
        if (str_starts_with($number, 'MK')) {
            $number = substr($number, 2);
        } elseif (mb_strpos($number, 'МК') === 0) { // Cyrillic МК
            $number = mb_substr($number, 2);
        }
        return $number;
    }
}
