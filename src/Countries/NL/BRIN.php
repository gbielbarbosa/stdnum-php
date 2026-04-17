<?php
namespace StdNum\Countries\NL;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class BRIN implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        $len = strlen($cleaned);
        if ($len !== 4 && $len !== 6) {
            return ValidationResult::failure('Invalid length for BRIN');
        }

        if (!preg_match('/^[0-9]{2}[A-Z]{2}([0-9]{2})?$/', $cleaned)) {
            return ValidationResult::failure('Invalid format for BRIN');
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
        return trim(strtoupper(str_replace([' ', '-', '.'], '', $number)));
    }
}
