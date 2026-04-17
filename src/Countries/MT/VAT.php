<?php
namespace StdNum\Countries\MT;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class VAT implements DocumentInterface
{
    use Cleanable;

    private function calcChecksum(string $number): int
    {
        $weights = [3, 4, 6, 7, 8, 9, 10, 1];
        $total = 0;
        for ($i = 0; $i < 8; $i++) {
            $total += $weights[$i] * (int)$number[$i];
        }
        return $total % 37;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned) || $cleaned[0] === '0') {
            return ValidationResult::failure('Invalid format for VAT');
        }

        if (strlen($cleaned) !== 8) {
            return ValidationResult::failure('Invalid length for VAT');
        }

        if ($this->calcChecksum($cleaned) !== 0) {
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
        if (str_starts_with($number, 'MT')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
