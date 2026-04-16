<?php

namespace StdNum\Countries\ES;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class CIF implements DocumentInterface
{
    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 9) {
            return ValidationResult::failure('Invalid length for a CIF number.');
        }

        if (!ctype_digit(substr($compact, 1, 7))) {
            return ValidationResult::failure('Invalid format for CIF.');
        }

        if (!in_array($compact[0], str_split('ABCDEFGHJNPQRSUVW'))) {
            return ValidationResult::failure('Invalid starting character for CIF.');
        }

        $validChecksums = $this->calcCheckDigits(substr($compact, 0, 8));
        if (!in_array($compact[8], $validChecksums)) {
            return ValidationResult::failure('Invalid checksum for CIF.');
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

        return $compact;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }

    protected function calcCheckDigits(string $base): array
    {
        // Luhn mod 10 over the 7 digits
        $digits = substr($base, 1, 7);
        $sum = 0;
        
        for ($i = 0; $i < strlen($digits); $i++) {
            $n = (int)$digits[$i];
            if ($i % 2 === 0) { // odd position from 1 (0-indexed here would be even) Let's think.
                // In CIF, positions 1, 3, 5, 7 are odd positions BUT string is 0-indexed.
                // 1st digit is index 0.
                $n *= 2;
                if ($n > 9) {
                    $n -= 9; // sum of digits
                }
            }
            $sum += $n;
        }
        
        $check = (10 - ($sum % 10)) % 10;
        
        $chars = 'JABCDEFGHI';
        return [(string)$check, $chars[$check]];
    }
}

