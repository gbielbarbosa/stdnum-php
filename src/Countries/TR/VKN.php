<?php
namespace StdNum\Countries\TR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class VKN implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $s = 0;
        $sub = substr($number, 0, 9);
        $rev = strrev($sub);
        
        for ($i = 1; $i <= 9; $i++) {
            $n = (int)$rev[$i - 1];
            $c1 = ($n + $i) % 10;
            if ($c1 > 0) {
                // bcpowmod to prevent integer overflow safely
                $pow = bcpowmod('2', (string)$i, '9');
                // if result is 0 it should be 9
                $c2 = ($c1 * (int)$pow) % 9 ?: 9;
                $s += $c2;
            }
        }

        return (string)((10 - ($s % 10)) % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for VKN');
        }

        if (strlen($cleaned) !== 10) {
            return ValidationResult::failure('Invalid length for VKN');
        }

        if ($this->calcCheckDigit($cleaned) !== $cleaned[9]) {
            return ValidationResult::failure('Invalid checksum for VKN');
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
