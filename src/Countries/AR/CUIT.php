<?php

namespace StdNum\Countries\AR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class CUIT implements DocumentInterface
{
    use Cleanable;

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $compact = $this->compact($number);
        if (strlen($compact) === 11) {
            return substr($compact, 0, 2) . '-' . substr($compact, 2, 8) . '-' . substr($compact, 10);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        return trim(str_replace([' ', '-'], '', $number));
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 11) {
            return ValidationResult::failure('Invalid length for CUIT');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for CUIT');
        }

        $type = substr($cleaned, 0, 2);
        if (!in_array($type, ['20', '23', '24', '27', '30', '33', '34', '50', '51', '55'])) {
            return ValidationResult::failure('Invalid component for CUIT');
        }

        $weights = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $weights[$i] * (int)$cleaned[$i];
        }

        $checkDigit = $sum % 11;
        $expected = "012345678990"[11 - $checkDigit];

        if ($expected !== $cleaned[10]) {
            return ValidationResult::failure('Invalid checksum for CUIT');
        }

        return ValidationResult::success();
    }
}
