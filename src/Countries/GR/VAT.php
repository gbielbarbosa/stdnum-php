<?php
namespace StdNum\Countries\GR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class VAT implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $checksum = 0;
        for ($i = 0; $i < strlen($number); $i++) {
            $checksum = $checksum * 2 + (int)$number[$i];
        }
        return (string)(($checksum * 2 % 11) % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for VAT');
        }

        if (strlen($cleaned) !== 9) {
            return ValidationResult::failure('Invalid length for VAT');
        }

        if ($cleaned[8] !== $this->calcCheckDigit(substr($cleaned, 0, 8))) {
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
        $number = trim(strtoupper(str_replace([' ', '-', '.', '/', ':'], '', $number)));
        if (str_starts_with($number, 'EL') || str_starts_with($number, 'GR')) {
            $number = substr($number, 2);
        }
        if (strlen($number) === 8) {
            $number = '0' . $number;
        }
        return $number;
    }
}
