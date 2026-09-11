<?php
namespace StdNum\Countries\FR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

/**
 * NIF (Numéro d'Immatriculation Fiscale, French tax identification number).
 *
 * The NIF (also known as numéro fiscal de référence or SPI) is a 13-digit
 * number issued by the French tax authorities to people for tax reporting
 * purposes. The first digit is 0, 1, 2, or 3, and the last 3 digits are
 * check digits calculated using modulo 511.
 */
class NIF implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigits(string $number): string
    {
        $base = (int)substr($number, 0, 10);
        $check = $base % 511;
        return str_pad((string)$check, 3, '0', STR_PAD_LEFT);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 13) {
            return ValidationResult::failure('Invalid length for NIF');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for NIF');
        }

        if (!in_array($cleaned[0], ['0', '1', '2', '3'], true)) {
            return ValidationResult::failure('Invalid component for NIF');
        }

        if ($this->calcCheckDigits($cleaned) !== substr($cleaned, 10, 3)) {
            return ValidationResult::failure('Invalid checksum for NIF');
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
        if (strlen($cleaned) === 13) {
            return substr($cleaned, 0, 2) . ' ' . substr($cleaned, 2, 2) . ' '
                 . substr($cleaned, 4, 3) . ' ' . substr($cleaned, 7, 3) . ' '
                 . substr($cleaned, 10, 3);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        return trim(str_replace(' ', '', $number));
    }
}
