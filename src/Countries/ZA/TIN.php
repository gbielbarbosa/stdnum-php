<?php
namespace StdNum\Countries\ZA;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;
use StdNum\Traits\LuhnChecksum;

class TIN implements DocumentInterface
{
    use Cleanable, LuhnChecksum;

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 10) {
            return ValidationResult::failure('Invalid length for TIN');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for TIN');
        }

        if (!in_array($cleaned[0], ['0', '1', '2', '3', '9'], true)) {
            return ValidationResult::failure('Invalid component for TIN');
        }

        if (!$this->verifyLuhn($cleaned)) {
            return ValidationResult::failure('Invalid checksum for TIN');
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
        return trim(strtoupper(str_replace([' ', '-', '/'], '', $number)));
    }
}
