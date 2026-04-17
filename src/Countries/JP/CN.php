<?php
namespace StdNum\Countries\JP;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class CN implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $weights = [1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2];
        $s = 0;
        $rev = strrev($number);
        for ($i = 0; $i < 12; $i++) {
            $s += $weights[$i] * (int)$rev[$i];
        }
        return (string)(9 - ($s % 9));
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 13) {
            return ValidationResult::failure('Invalid length for CN');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for CN');
        }

        if ($this->calcCheckDigit(substr($cleaned, 1)) !== $cleaned[0]) {
            return ValidationResult::failure('Invalid checksum for CN');
        }

        return ValidationResult::success();
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $compact = $this->compact($number);
        if (strlen($compact) === 13) {
            return $compact[0] . '-' . substr($compact, 1, 4) . '-' . substr($compact, 5, 4) . '-' . substr($compact, 9, 4);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}
