<?php
namespace StdNum\Countries\NL;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class Onderwijsnummer implements DocumentInterface
{
    use Cleanable;

    private function calcChecksum(string $number): int
    {
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += (int)$number[$i] * (9 - $i);
        }
        $sum -= (int)$number[8];
        return $sum % 11;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned) || (int)$cleaned <= 0) {
            return ValidationResult::failure('Invalid format for Onderwijsnummer');
        }

        if (!str_starts_with($cleaned, '10')) {
            return ValidationResult::failure('Invalid format for Onderwijsnummer');
        }

        if (strlen($cleaned) !== 9) {
            return ValidationResult::failure('Invalid length for Onderwijsnummer');
        }

        if ($this->calcChecksum($cleaned) !== 5) {
            return ValidationResult::failure('Invalid checksum for Onderwijsnummer');
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
        return trim(strtoupper(str_replace([' ', '-', '.'], '', $number)));
    }
}
