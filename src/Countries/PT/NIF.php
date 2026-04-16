<?php

namespace StdNum\Countries\PT;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class NIF implements DocumentInterface
{
    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 9) {
            return ValidationResult::failure('Invalid length for PT NIF.');
        }

        if (!ctype_digit($compact) || $compact[0] === '0') {
            return ValidationResult::failure('Invalid format or starting character for PT NIF.');
        }

        if ($this->calcCheckDigit(substr($compact, 0, 8)) !== $compact[8]) {
            return ValidationResult::failure('Invalid checksum for PT NIF.');
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
        if (strlen($compact) !== 9) {
            return $number;
        }
        return substr($compact, 0, 3) . ' ' . substr($compact, 3, 3) . ' ' . substr($compact, 6);
    }

    public function compact(string $number): string
    {
        $compact = trim(strtoupper(str_replace([' ', '-', '.'], '', $number)));
        if (str_starts_with($compact, 'PT')) {
            $compact = substr($compact, 2);
        }
        return $compact;
    }

    protected function calcCheckDigit(string $base): string
    {
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += (int)$base[$i] * (9 - $i);
        }
        
        $modulo = $sum % 11;
        $check = 11 - $modulo;
        if ($check >= 10) {
            $check = 0;
        }

        return (string)$check;
    }
}

