<?php
namespace StdNum\Countries\KR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class BRN implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 10) {
            return ValidationResult::failure('Invalid length for BRN');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for BRN');
        }

        if (substr($cleaned, 0, 3) < '101' || substr($cleaned, 3, 2) === '00' || substr($cleaned, 5, 4) === '0000') {
            return ValidationResult::failure('Invalid component for BRN');
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
        if (strlen($compact) === 10) {
            return substr($compact, 0, 3) . '-' . substr($compact, 3, 2) . '-' . substr($compact, 5);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}
