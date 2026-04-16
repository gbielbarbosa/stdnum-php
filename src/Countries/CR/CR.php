<?php
namespace StdNum\Countries\CR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class CR implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 11 && strlen($cleaned) !== 12) {
            return ValidationResult::failure('Invalid length for CR');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for CR');
        }

        if ($cleaned[0] !== '1') {
            return ValidationResult::failure('Invalid component for CR');
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
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}
