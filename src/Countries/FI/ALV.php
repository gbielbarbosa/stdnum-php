<?php
namespace StdNum\Countries\FI;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class ALV implements DocumentInterface
{
    use Cleanable;

    private function checksum(string $number): int
    {
        $weights = [7, 9, 10, 5, 8, 4, 2, 1];
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return $sum % 11;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for ALV');
        }

        if (strlen($cleaned) !== 8) {
            return ValidationResult::failure('Invalid length for ALV');
        }

        if ($this->checksum($cleaned) !== 0) {
            return ValidationResult::failure('Invalid checksum for ALV');
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
        if (str_starts_with($number, 'FI')) {
            return substr($number, 2);
        }
        return $number;
    }
}
