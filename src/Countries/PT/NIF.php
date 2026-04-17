<?php
namespace StdNum\Countries\PT;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class NIF implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += (9 - $i) * (int)$number[$i];
        }
        return (string)((11 - $sum % 11) % 11 % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned) || str_starts_with($cleaned, '0')) {
            return ValidationResult::failure('Invalid format for NIF');
        }

        if (strlen($cleaned) !== 9) {
            return ValidationResult::failure('Invalid length for NIF');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, -1)) !== $cleaned[8]) {
            return ValidationResult::failure('Invalid checksum for NIF');
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
        $number = trim(strtoupper(str_replace([' ', '-', '.'], '', $number)));
        if (str_starts_with($number, 'PT')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
