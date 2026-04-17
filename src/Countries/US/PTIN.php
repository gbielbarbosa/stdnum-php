<?php
namespace StdNum\Countries\US;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class PTIN implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $cleaned = strtoupper($this->compact($number));

        if (!preg_match('/^P[0-9]{8}$/', $cleaned)) {
            return ValidationResult::failure('Invalid format for PTIN');
        }

        return ValidationResult::success();
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        return strtoupper($this->compact($number));
    }

    public function compact(string $number): string
    {
        return trim(str_replace('-', '', $number));
    }
}
