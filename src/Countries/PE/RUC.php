<?php
namespace StdNum\Countries\PE;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class RUC implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $weights = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return (string)(((11 - $sum % 11) % 11) % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 11) {
            return ValidationResult::failure('Invalid length for RUC');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for RUC');
        }

        $prefix = substr($cleaned, 0, 2);
        if (!in_array($prefix, ['10', '15', '17', '20'], true)) {
            return ValidationResult::failure('Invalid component for RUC');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, 10)) !== $cleaned[10]) {
            return ValidationResult::failure('Invalid checksum for RUC');
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
        return trim(str_replace(' ', '', $number));
    }
}
