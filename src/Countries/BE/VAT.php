<?php

namespace StdNum\Countries\BE;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class VAT implements DocumentInterface
{
    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 10) {
            return ValidationResult::failure('Invalid length for Belgian VAT.');
        }

        if (!ctype_digit($compact) || (int)$compact <= 0) {
            return ValidationResult::failure('VAT must contain only digits.');
        }

        if ($compact[0] !== '0' && $compact[0] !== '1') {
            return ValidationResult::failure('Invalid base for Belgian VAT.');
        }

        $base = (int)substr($compact, 0, 8);
        $check = (int)substr($compact, 8, 2);

        if (($base + $check) % 97 !== 0) {
            return ValidationResult::failure('Invalid checksum for Belgian VAT.');
        }

        return ValidationResult::success();
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        // No specific punctuation standard commonly enforces strict separation
        return $this->compact($number);
    }

    public function compact(string $number): string
    {
        $compact = trim(strtoupper(str_replace([' ', '-', '.', '/'], '', $number)));
        if (str_starts_with($compact, 'BE')) {
            $compact = substr($compact, 2);
        }
        if (str_starts_with($compact, '(0)')) {
            $compact = '0' . substr($compact, 3);
        }
        if (strlen($compact) === 9) {
            $compact = '0' . $compact;
        }

        return $compact;
    }
}
