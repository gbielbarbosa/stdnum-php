<?php
namespace StdNum\Countries\SI;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class DDV implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $sum = 0;
        for ($i = 0; $i < 7; $i++) {
            $sum += (8 - $i) * (int)$number[$i];
        }
        $check = 11 - ($sum % 11);
        if ($check === 10) {
            return '0';
        }
        return (string)$check;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned) || str_starts_with($cleaned, '0')) {
            return ValidationResult::failure('Invalid format for DDV');
        }

        if (strlen($cleaned) !== 8) {
            return ValidationResult::failure('Invalid length for DDV');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, -1)) !== substr($cleaned, -1)) {
            return ValidationResult::failure('Invalid checksum for DDV');
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
        if (str_starts_with($number, 'SI')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
