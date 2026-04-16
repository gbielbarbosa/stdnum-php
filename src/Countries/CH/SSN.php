<?php
namespace StdNum\Countries\CH;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class SSN implements DocumentInterface
{
    use Cleanable;

    private function calcEan13(string $number): string
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $weight = ($i % 2 === 0) ? 1 : 3;
            $sum += $weight * (int)$number[$i];
        }
        return (string)((10 - ($sum % 10)) % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 13) {
            return ValidationResult::failure('Invalid length for Swiss SSN');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for Swiss SSN');
        }

        if (!str_starts_with($cleaned, '756')) {
            return ValidationResult::failure('Invalid component for Swiss SSN');
        }

        if ($cleaned[12] !== $this->calcEan13(substr($cleaned, 0, 12))) {
            return ValidationResult::failure('Invalid checksum for Swiss SSN');
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
        if (strlen($compact) === 13) {
            return substr($compact, 0, 3) . '.' . substr($compact, 3, 4) . '.' . substr($compact, 7, 4) . '.' . substr($compact, 11);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        return trim(str_replace([' ', '.'], '', $number));
    }
}
