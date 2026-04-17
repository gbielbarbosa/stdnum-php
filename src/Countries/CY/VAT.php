<?php
namespace StdNum\Countries\CY;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class VAT implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $translation = [
            '0' => 1, '1' => 0, '2' => 5, '3' => 7, '4' => 9,
            '5' => 13, '6' => 15, '7' => 17, '8' => 19, '9' => 21,
        ];
        $sum = 0;
        for ($i = 0; $i < strlen($number); $i++) {
            if ($i % 2 === 0) {
                $sum += $translation[$number[$i]];
            } else {
                $sum += (int)$number[$i];
            }
        }
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return $alphabet[$sum % 26];
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 9) {
            return ValidationResult::failure('Invalid length for VAT');
        }

        if (!ctype_digit(substr($cleaned, 0, -1))) {
            return ValidationResult::failure('Invalid format for VAT');
        }

        if (str_starts_with($cleaned, '12')) {
            return ValidationResult::failure('Invalid component for VAT');
        }

        if ($cleaned[8] !== $this->calcCheckDigit(substr($cleaned, 0, -1))) {
            return ValidationResult::failure('Invalid checksum for VAT');
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
        if (str_starts_with($number, 'CY')) {
            return substr($number, 2);
        }
        return $number;
    }
}
