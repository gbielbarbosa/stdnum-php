<?php

namespace StdNum\Countries\IT;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class CodiceFiscale implements DocumentInterface
{
    protected array $evenValues = [
        '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
        'A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4, 'F' => 5, 'G' => 6, 'H' => 7, 'I' => 8, 'J' => 9,
        'K' => 10, 'L' => 11, 'M' => 12, 'N' => 13, 'O' => 14, 'P' => 15, 'Q' => 16, 'R' => 17, 'S' => 18,
        'T' => 19, 'U' => 20, 'V' => 21, 'W' => 22, 'X' => 23, 'Y' => 24, 'Z' => 25
    ];

    protected array $oddValues = [
        '0' => 1, '1' => 0, '2' => 5, '3' => 7, '4' => 9, '5' => 13, '6' => 15, '7' => 17, '8' => 19, '9' => 21,
        'A' => 1, 'B' => 0, 'C' => 5, 'D' => 7, 'E' => 9, 'F' => 13, 'G' => 15, 'H' => 17, 'I' => 19, 'J' => 21,
        'K' => 2, 'L' => 4, 'M' => 18, 'N' => 20, 'O' => 11, 'P' => 3, 'Q' => 6, 'R' => 8, 'S' => 12, 'T' => 14,
        'U' => 16, 'V' => 10, 'W' => 22, 'X' => 25, 'Y' => 24, 'Z' => 23
    ];

    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) === 11) {
            // Can be a company matching IVA
            return (new IVA())->validate($compact);
        }

        if (strlen($compact) !== 16) {
            return ValidationResult::failure('Invalid length for Codice Fiscale.');
        }

        $pattern = '/^[A-Z]{6}[0-9LMNPQRSTUV]{2}[ABCDEHLMPRST]{1}[0-9LMNPQRSTUV]{2}[A-Z]{1}[0-9LMNPQRSTUV]{3}[A-Z]{1}$/i';
        if (!preg_match($pattern, $compact)) {
            return ValidationResult::failure('Invalid format for Codice Fiscale.');
        }

        if ($this->calcCheckDigit(substr($compact, 0, 15)) !== $compact[15]) {
            return ValidationResult::failure('Invalid checksum for Codice Fiscale.');
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
        return trim(strtoupper(str_replace([' ', '-', ':'], '', $number)));
    }

    protected function calcCheckDigit(string $base): string
    {
        $sum = 0;
        for ($i = 0; $i < 15; $i++) {
            $char = $base[$i];
            // $i is 0-indexed. 0 is the 1st position (odd position mathematically).
            if ($i % 2 === 0) {
                $sum += $this->oddValues[$char];
            } else {
                $sum += $this->evenValues[$char];
            }
        }
        
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return $chars[$sum % 26];
    }
}

