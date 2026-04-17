<?php
namespace StdNum\Countries\LT;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class PVM implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $sum = 0;
        for ($i = 0; $i < strlen($number); $i++) {
            $sum += (1 + ($i % 9)) * (int)$number[$i];
        }
        $check = $sum % 11;

        if ($check === 10) {
            $sum = 0;
            for ($i = 0; $i < strlen($number); $i++) {
                $sum += (1 + (($i + 2) % 9)) * (int)$number[$i];
            }
            $check = $sum % 11;
        }

        return (string)($check % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for PVM');
        }

        if (strlen($cleaned) === 9) {
            if ($cleaned[7] !== '1') {
                return ValidationResult::failure('Invalid component for PVM');
            }
        } elseif (strlen($cleaned) === 12) {
            if ($cleaned[10] !== '1') {
                return ValidationResult::failure('Invalid component for PVM');
            }
        } else {
            return ValidationResult::failure('Invalid length for PVM');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, -1)) !== substr($cleaned, -1)) {
            return ValidationResult::failure('Invalid checksum for PVM');
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
        if (str_starts_with($number, 'LT')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
