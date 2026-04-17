<?php
namespace StdNum\Countries\IN;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;
use StdNum\Algorithms\Verhoeff;

class AADHAAR implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 12) {
            return ValidationResult::failure('Invalid length for AADHAAR');
        }

        if (!preg_match('/^[2-9][0-9]{11}$/', $cleaned)) {
            return ValidationResult::failure('Invalid format for AADHAAR');
        }

        if ($cleaned === strrev($cleaned)) {
            return ValidationResult::failure('Invalid format for AADHAAR');
        }

        if (!Verhoeff::validate($cleaned)) {
            return ValidationResult::failure('Invalid checksum for AADHAAR');
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
        if (strlen($compact) >= 12) {
            return substr($compact, 0, 4) . ' ' . substr($compact, 4, 4) . ' ' . substr($compact, 8);
        }
        return $number;
    }

    public function mask(string $number): string
    {
        $compact = $this->compact($number);
        if (strlen($compact) >= 4) {
            return 'XXXX XXXX ' . substr($compact, -4);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}
