<?php
namespace StdNum\Countries\MD;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class IDNO implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $weights = [7, 3, 1, 7, 3, 1, 7, 3, 1, 7, 3, 1];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return (string)($sum % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for IDNO');
        }

        if (strlen($cleaned) !== 13) {
            return ValidationResult::failure('Invalid length for IDNO');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, 12)) !== $cleaned[12]) {
            return ValidationResult::failure('Invalid checksum for IDNO');
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
        return trim(strtoupper(str_replace([' '], '', $number)));
    }
}
