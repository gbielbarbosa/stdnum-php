<?php
namespace StdNum\Countries\AZ;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class VOEN implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $weights = [4, 1, 8, 6, 2, 7, 5, 3];
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return (string)($sum % 11);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 10) {
            return ValidationResult::failure('Invalid length for VÖEN');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for VÖEN');
        }

        if (!in_array($cleaned[9], ['1', '2'])) {
            return ValidationResult::failure('Invalid component for VÖEN');
        }

        if ($cleaned[8] !== $this->calcCheckDigit($cleaned)) {
            return ValidationResult::failure('Invalid checksum for VÖEN');
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
        $number = trim(str_replace(' ', '', $number));
        if (strlen($number) === 9) {
            $number = '0' . $number;
        }
        return $number;
    }
}
