<?php

namespace StdNum\Countries\ES;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class DNI implements DocumentInterface
{
    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 9) {
            return ValidationResult::failure('Invalid length for a DNI number.');
        }

        if (!ctype_digit(substr($compact, 0, 8))) {
            return ValidationResult::failure('Invalid format for DNI.');
        }

        if ($compact[8] !== $this->calcCheckDigit(substr($compact, 0, 8))) {
            return ValidationResult::failure('Invalid checksum for DNI.');
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

        return substr($compact, 0, 8) . '-' . substr($compact, 8);
    }

    public function compact(string $number): string
    {
        $number = trim(strtoupper(str_replace([' ', '-'], '', $number)));
        if (strlen($number) < 9 && ctype_digit(substr($number, 0, -1))) {
            // pad with zeros
            $number = str_pad(substr($number, 0, -1), 8, '0', STR_PAD_LEFT) . substr($number, -1);
        }
        return $number;
    }

    public function calcCheckDigit(string $number): string
    {
        $chars = 'TRWAGMYFPDXBNJZSQVHLCKE';
        return $chars[(int)$number % 23];
    }
}

