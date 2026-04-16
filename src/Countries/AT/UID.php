<?php

namespace StdNum\Countries\AT;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class UID implements DocumentInterface
{
    use Cleanable;

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $compact = $this->compact($number);
        if (strlen($compact) === 9) {
            return 'AT ' . $compact;
        }
        return $number;
    }

    public function compact(string $number): string
    {
        $number = trim(strtoupper(str_replace([' ', '-', '.', '/'], '', $number)));
        if (str_starts_with($number, 'AT')) {
            return substr($number, 2);
        }
        return $number;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 9) {
            return ValidationResult::failure('Invalid length for Austrian UID');
        }

        if ($cleaned[0] !== 'U' || !ctype_digit(substr($cleaned, 1))) {
            return ValidationResult::failure('Invalid format for Austrian UID');
        }

        $target = substr($cleaned, 1, 7);
        $sum = 0;
        $weight = 1;
        for ($i = 0; $i < 7; $i++) {
            $v = (int)$target[$i] * $weight;
            if ($v > 9) {
                $v -= 9;
            }
            $sum += $v;
            $weight = $weight === 1 ? 2 : 1;
        }

        $expected = (string)((100 + 6 - $sum) % 10);

        if ($expected !== $cleaned[8]) {
            return ValidationResult::failure('Invalid checksum for Austrian UID');
        }

        return ValidationResult::success();
    }
}
