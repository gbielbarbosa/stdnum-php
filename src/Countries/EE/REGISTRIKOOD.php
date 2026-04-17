<?php
namespace StdNum\Countries\EE;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class REGISTRIKOOD implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for REGISTRIKOOD');
        }

        if (strlen($cleaned) !== 8) {
            return ValidationResult::failure('Invalid length for REGISTRIKOOD');
        }

        if (!in_array($cleaned[0], ['1', '7', '8', '9'])) {
            return ValidationResult::failure('Invalid component for REGISTRIKOOD');
        }

        $ik = new IK();
        if ($cleaned[7] !== $ik->calcCheckDigit($cleaned)) {
            return ValidationResult::failure('Invalid checksum for REGISTRIKOOD');
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
