<?php

namespace StdNum\Countries\ES;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class NIE implements DocumentInterface
{
    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 9) {
            return ValidationResult::failure('Invalid length for a NIE number.');
        }

        if (!in_array($compact[0], ['X', 'Y', 'Z'])) {
            return ValidationResult::failure('Invalid starting character for NIE.');
        }

        if (!ctype_digit(substr($compact, 1, 7))) {
            return ValidationResult::failure('Invalid format for NIE.');
        }

        if ($compact[8] !== $this->calcCheckDigit(substr($compact, 0, 8))) {
            return ValidationResult::failure('Invalid checksum for NIE.');
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

        return substr($compact, 0, 1) . '-' . substr($compact, 1, 7) . '-' . substr($compact, 8);
    }

    public function compact(string $number): string
    {
        $number = trim(strtoupper(str_replace([' ', '-'], '', $number)));
        // NIE always has 9 characters, starting with X Y Z and 7 digits
        // Some formats might not pad zeros, standard is 9.
        return $number;
    }

    public function calcCheckDigit(string $number): string
    {
        $map = ['X' => '0', 'Y' => '1', 'Z' => '2'];
        $parsed = strtr($number[0], $map) . substr($number, 1);
        
        $chars = 'TRWAGMYFPDXBNJZSQVHLCKE';
        return $chars[(int)$parsed % 23];
    }
}

