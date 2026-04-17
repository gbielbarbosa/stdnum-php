<?php
namespace StdNum\Countries\NO;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class OrgNr implements DocumentInterface
{
    use Cleanable;

    private function calcChecksum(string $number): int
    {
        $weights = [3, 2, 7, 6, 5, 4, 3, 2, 1];
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return $sum % 11;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for OrgNr');
        }

        if (strlen($cleaned) !== 9) {
            return ValidationResult::failure('Invalid length for OrgNr');
        }

        if ($this->calcChecksum($cleaned) !== 0) {
            return ValidationResult::failure('Invalid checksum for OrgNr');
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
            return substr($cleaned, 0, 3) . ' ' . substr($cleaned, 3, 3) . ' ' . substr($cleaned, 6);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        return trim(str_replace(' ', '', $number));
    }
}
