<?php
namespace StdNum\Countries\PL;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class NIP implements DocumentInterface
{
    use Cleanable;

    private function calcChecksum(string $number): int
    {
        $weights = [6, 5, 7, 2, 3, 4, 5, 6, 7, -1];
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return $sum % 11;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for NIP');
        }

        if (strlen($cleaned) !== 10) {
            return ValidationResult::failure('Invalid length for NIP');
        }

        if ($this->calcChecksum($cleaned) !== 0) {
            return ValidationResult::failure('Invalid checksum for NIP');
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
        if (strlen($cleaned) === 10) {
            return substr($cleaned, 0, 3) . '-' . substr($cleaned, 3, 3) . '-' . substr($cleaned, 6, 2) . '-' . substr($cleaned, 8);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        $number = trim(strtoupper(str_replace([' ', '-'], '', $number)));
        if (str_starts_with($number, 'PL')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
