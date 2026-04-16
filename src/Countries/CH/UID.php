<?php
namespace StdNum\Countries\CH;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class UID implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $weights = [5, 4, 3, 2, 7, 6, 5, 4];
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return (string)((11 - ($sum % 11)) % 11);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 12) {
            return ValidationResult::failure('Invalid length for Swiss UID');
        }

        if (!str_starts_with($cleaned, 'CHE')) {
            return ValidationResult::failure('Invalid component for Swiss UID');
        }

        $digits = substr($cleaned, 3);
        if (!ctype_digit($digits)) {
            return ValidationResult::failure('Invalid format for Swiss UID');
        }

        if ($cleaned[11] !== $this->calcCheckDigit(substr($cleaned, 3, 8))) {
            return ValidationResult::failure('Invalid checksum for Swiss UID');
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
        if (strlen($compact) === 12) {
            return substr($compact, 0, 3) . '-' . substr($compact, 3, 3) . '.' . substr($compact, 6, 3) . '.' . substr($compact, 9, 3);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        $number = trim(strtoupper(str_replace([' ', '-', '.'], '', $number)));
        if (strlen($number) === 9 && ctype_digit($number)) {
            $number = 'CHE' . $number;
        }
        return $number;
    }
}
