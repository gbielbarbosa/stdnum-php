<?php
namespace StdNum\Countries\IS;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class VSK implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for VSK');
        }

        if (strlen($cleaned) !== 5 && strlen($cleaned) !== 6) {
            return ValidationResult::failure('Invalid length for VSK');
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
        $number = trim(strtoupper(str_replace([' '], '', $number)));
        if (str_starts_with($number, 'IS')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
