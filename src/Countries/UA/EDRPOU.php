<?php
namespace StdNum\Countries\UA;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class EDRPOU implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $weights = [1, 2, 3, 4, 5, 6, 7];
        if (in_array($number[0], ['3', '4', '5'], true)) {
            $weights = [7, 1, 2, 3, 4, 5, 6];
        }

        $total = 0;
        for ($i = 0; $i < 7; $i++) {
            $total += $weights[$i] * (int)$number[$i];
        }

        if ($total % 11 < 10) {
            return (string)($total % 11);
        }

        // Calculate again with other weights (+2)
        $weights = array_map(function ($w) {
            return $w + 2;
        }, $weights);

        $total = 0;
        for ($i = 0; $i < 7; $i++) {
            $total += $weights[$i] * (int)$number[$i];
        }

        return (string)(($total % 11) % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 8) {
            return ValidationResult::failure('Invalid length for EDRPOU');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for EDRPOU');
        }

        if ($this->calcCheckDigit($cleaned) !== $cleaned[7]) {
            return ValidationResult::failure('Invalid checksum for EDRPOU');
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
