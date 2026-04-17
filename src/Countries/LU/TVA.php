<?php
namespace StdNum\Countries\LU;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class TVA implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigits(string $number): string
    {
        return sprintf('%02d', (int)$number % 89);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for TVA');
        }

        if (strlen($cleaned) !== 8) {
            return ValidationResult::failure('Invalid length for TVA');
        }

        if ($this->calcCheckDigits(substr($cleaned, 0, 6)) !== substr($cleaned, 6, 2)) {
            return ValidationResult::failure('Invalid checksum for TVA');
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
        $number = trim(strtoupper(str_replace([' ', ':', '.', '-'], '', $number)));
        if (str_starts_with($number, 'LU')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
