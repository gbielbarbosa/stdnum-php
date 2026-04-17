<?php
namespace StdNum\Countries\VE;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class RIF implements DocumentInterface
{
    use Cleanable;

    private array $companyTypes = [
        'V' => 4,
        'E' => 8,
        'J' => 12,
        'P' => 16,
        'G' => 20,
    ];

    private function calcCheckDigit(string $number): string
    {
        $weights = [3, 2, 7, 6, 5, 4, 3, 2];
        $c = $this->companyTypes[$number[0]];

        for ($i = 0; $i < 8; $i++) {
            $c += $weights[$i] * (int)$number[$i + 1];
        }

        $chars = '00987654321';
        return $chars[$c % 11];
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 10) {
            return ValidationResult::failure('Invalid length for RIF');
        }

        if (!isset($this->companyTypes[$cleaned[0]])) {
            return ValidationResult::failure('Invalid component for RIF');
        }

        if (!ctype_digit(substr($cleaned, 1))) {
            return ValidationResult::failure('Invalid format for RIF');
        }

        if ($this->calcCheckDigit($cleaned) !== $cleaned[9]) {
            return ValidationResult::failure('Invalid checksum for RIF');
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
