<?php
namespace StdNum\Countries\SV;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class NIT implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $seq = substr($number, 10, 3);
        if ($seq <= '100') {
            $weights = [14, 13, 12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2];
            $total = 0;
            for ($i = 0; $i < 13; $i++) {
                $total += $weights[$i] * (int)$number[$i];
            }
            return (string)(($total % 11) % 10);
        }

        $weights = [2, 7, 6, 5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $total = 0;
        for ($i = 0; $i < 13; $i++) {
            $total += $weights[$i] * (int)$number[$i];
        }
        
        $s = -$total % 11;
        if ($s < 0) {
            $s += 11;
        }

        return (string)($s % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 14) {
            return ValidationResult::failure('Invalid length for NIT');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for NIT');
        }

        if (!in_array($cleaned[0], ['0', '1', '9'], true)) {
            return ValidationResult::failure('Invalid component for NIT');
        }

        if ($this->calcCheckDigit($cleaned) !== $cleaned[13]) {
            return ValidationResult::failure('Invalid checksum for NIT');
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
        if (strlen($cleaned) === 14) {
            return substr($cleaned, 0, 4) . '-' . substr($cleaned, 4, 6) . '-' . substr($cleaned, 10, 3) . '-' . substr($cleaned, 13, 1);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        $number = trim(strtoupper(str_replace([' ', '-'], '', $number)));
        if (str_starts_with($number, 'SV')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
