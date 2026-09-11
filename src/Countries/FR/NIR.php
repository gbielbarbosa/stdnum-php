<?php
namespace StdNum\Countries\FR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

/**
 * NIR (Numéro d'Inscription au Répertoire, French personal identification number).
 *
 * The NIR is used to identify persons in France and is commonly known as the
 * "social security number". The number consists of 15 characters: the first
 * digit indicates gender (1=M, 2=F), followed by 2 digits for year of birth,
 * 2 for the month, 5 for the location (COG, may contain '2A' or '2B' for Corsica),
 * 3 for a serial and 2 check digits.
 *
 * The check digits are calculated as: 97 - (first_13_digits % 97).
 * For Corsica, '2A' is replaced with '19' and '2B' with '18' before calculation.
 */
class NIR implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 15) {
            return ValidationResult::failure('Invalid length for NIR');
        }

        // Allow 2A/2B for Corsica in department position
        $forCheck = $cleaned;
        if (str_contains($forCheck, '2A')) {
            $forCheck = str_replace('2A', '19', $forCheck);
        } elseif (str_contains($forCheck, '2B')) {
            $forCheck = str_replace('2B', '18', $forCheck);
        }

        if (!ctype_digit($forCheck)) {
            return ValidationResult::failure('Invalid format for NIR');
        }

        if (!in_array($cleaned[0], ['1', '2'], true)) {
            return ValidationResult::failure('Invalid component for NIR');
        }

        // Check digits: 97 - (first 13 digits mod 97) == last 2 digits
        $base = bcmod(substr($forCheck, 0, 13), '97');
        $expected = 97 - (int)$base;
        $actual = (int)substr($forCheck, 13, 2);

        if ($expected !== $actual) {
            return ValidationResult::failure('Invalid checksum for NIR');
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
        if (strlen($cleaned) === 15) {
            return substr($cleaned, 0, 1) . ' ' . substr($cleaned, 1, 2) . ' '
                 . substr($cleaned, 3, 2) . ' ' . substr($cleaned, 5, 2) . ' '
                 . substr($cleaned, 7, 3) . ' ' . substr($cleaned, 10, 3) . ' '
                 . substr($cleaned, 13, 2);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        return strtoupper(trim(str_replace([' ', '-'], '', $number)));
    }
}
