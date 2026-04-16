<?php

namespace StdNum\Countries\AL;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class NIPT implements DocumentInterface
{
    use Cleanable;

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
        $number = trim(strtoupper(str_replace(' ', '', $number)));
        if (str_starts_with($number, '(AL)')) {
            return substr($number, 4);
        } elseif (str_starts_with($number, 'AL')) {
            return substr($number, 2);
        }
        return $number;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 10) {
            return ValidationResult::failure('Invalid length for NIPT');
        }

        if (!preg_match('/^[A-M][0-9]{8}[A-Z]$/', $cleaned)) {
            return ValidationResult::failure('Invalid format for NIPT');
        }

        return ValidationResult::success();
    }
}
