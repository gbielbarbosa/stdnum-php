<?php
namespace StdNum\Countries\US;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class ATIN implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $raw = trim(str_replace(' ', '', $number));

        if (!preg_match('/^[0-9]{3}-?[0-9]{2}-?[0-9]{4}$/', $raw)) {
            return ValidationResult::failure('Invalid format for ATIN');
        }

        return ValidationResult::success();
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $cleaned = $this->compact($number);
        if (strlen($cleaned) === 9) {
            return substr($cleaned, 0, 3) . '-' . substr($cleaned, 3, 2) . '-' . substr($cleaned, 5);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        return trim(str_replace('-', '', $number));
    }
}
