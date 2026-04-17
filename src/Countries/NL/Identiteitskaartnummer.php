<?php
namespace StdNum\Countries\NL;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class Identiteitskaartnummer implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 9) {
            return ValidationResult::failure('Invalid length for Identiteitskaartnummer');
        }

        if (!preg_match('/^[A-Z]{2}[0-9A-Z]{6}[0-9]$/', $cleaned)) {
            return ValidationResult::failure('Invalid format for Identiteitskaartnummer');
        }

        if (strpos($cleaned, 'O') !== false) {
            return ValidationResult::failure("The letter 'O' is not allowed");
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
        return trim(strtoupper(str_replace(' ', '', $number)));
    }
}
