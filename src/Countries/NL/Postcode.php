<?php
namespace StdNum\Countries\NL;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class Postcode implements DocumentInterface
{
    use Cleanable;

    private array $blacklist = ['SA', 'SD', 'SS'];

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!preg_match('/^([1-9][0-9]{3})([A-Z]{2})$/', $cleaned, $matches)) {
            return ValidationResult::failure('Invalid format for Postcode');
        }

        if (in_array($matches[2], $this->blacklist, true)) {
            return ValidationResult::failure('Invalid component for Postcode');
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
        if (preg_match('/^([1-9][0-9]{3})([A-Z]{2})$/', $cleaned, $matches)) {
            return $matches[1] . ' ' . $matches[2];
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        $number = trim(strtoupper(str_replace([' ', '-'], '', $number)));
        if (str_starts_with($number, 'NL')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
