<?php
namespace StdNum\Countries\IE;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class PPS implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!preg_match('/^\d{7}[A-W][ABHWTX]?$/', $cleaned)) {
            return ValidationResult::failure('Invalid format for PPS');
        }

        $vat = new VAT();
        
        if (strlen($cleaned) === 9 && in_array($cleaned[8], ['A', 'B', 'H'])) {
            if ($cleaned[7] !== $vat->calcCheckDigit(substr($cleaned, 0, 7) . $cleaned[8])) {
                return ValidationResult::failure('Invalid checksum for PPS');
            }
        } else {
            if ($cleaned[7] !== $vat->calcCheckDigit(substr($cleaned, 0, 7))) {
                return ValidationResult::failure('Invalid checksum for PPS');
            }
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
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}
