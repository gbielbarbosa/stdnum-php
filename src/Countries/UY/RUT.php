<?php
namespace StdNum\Countries\UY;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class RUT implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $weights = [4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $total = 0;
        for ($i = 0; $i < 11; $i++) {
            $total += $weights[$i] * (int)$number[$i];
        }

        $s = (-$total) % 11;
        if ($s < 0) {
            $s += 11;
        }

        return (string)$s;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 12) {
            return ValidationResult::failure('Invalid length for RUT');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for RUT');
        }

        $prefix = substr($cleaned, 0, 2);
        if ($prefix < '01' || $prefix > '22') {
            return ValidationResult::failure('Invalid component for RUT');
        }

        if (substr($cleaned, 2, 6) === '000000') {
            return ValidationResult::failure('Invalid component for RUT');
        }

        if (substr($cleaned, 8, 3) !== '001') {
            return ValidationResult::failure('Invalid component for RUT');
        }

        if ($this->calcCheckDigit($cleaned) !== $cleaned[11]) {
            return ValidationResult::failure('Invalid checksum for RUT');
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
        if (strlen($cleaned) === 12) {
            return substr($cleaned, 0, 2) . '-' . substr($cleaned, 2, 6) . '-' . substr($cleaned, 8, 3) . '-' . substr($cleaned, 11);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        $number = trim(strtoupper(str_replace([' ', '-'], '', $number)));
        if (str_starts_with($number, 'UY')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
