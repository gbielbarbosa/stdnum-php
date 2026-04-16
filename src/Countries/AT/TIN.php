<?php

namespace StdNum\Countries\AT;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class TIN implements DocumentInterface
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
            return substr($compact, 0, 2) . '-' . substr($compact, 2, 3) . '/' . substr($compact, 5);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        return trim(str_replace([' ', '-', '.', '/', ','], '', $number));
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 9) {
            return ValidationResult::failure('Invalid length for Austrian TIN');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for Austrian TIN');
        }

        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $n = (int)$cleaned[$i];
            if ($i % 2 !== 0) {
                // Odd positions mapped
                $mapped = "0246813579"[$n];
                $sum += (int)$mapped;
            } else {
                $sum += $n;
            }
        }

        $expected = (string)((10 - ($sum % 10)) % 10);
        
        if ($expected !== $cleaned[8]) {
            return ValidationResult::failure('Invalid checksum for Austrian TIN');
        }

        return ValidationResult::success();
    }
}
