<?php
namespace StdNum\Countries\CN;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class USCC implements DocumentInterface
{
    use Cleanable;

    private $alphabet = '0123456789ABCDEFGHJKLMNPQRTUWXY';

    private function calcCheckDigit(string $number): string
    {
        $weights = [1, 3, 9, 27, 19, 26, 16, 17, 20, 29, 25, 13, 8, 24, 10, 30, 28];
        $total = 0;
        for ($i = 0; $i < 17; $i++) {
            $total += strpos($this->alphabet, $number[$i]) * $weights[$i];
        }
        return $this->alphabet[(31 - ($total % 31)) % 31];
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 18) {
            return ValidationResult::failure('Invalid length for USCC');
        }

        if (!ctype_digit(substr($cleaned, 0, 8))) {
            return ValidationResult::failure('Invalid format for USCC');
        }

        for ($i = 8; $i < 18; $i++) {
            if (strpos($this->alphabet, $cleaned[$i]) === false) {
                return ValidationResult::failure('Invalid format for USCC');
            }
        }

        if ($cleaned[17] !== $this->calcCheckDigit($cleaned)) {
            return ValidationResult::failure('Invalid checksum for USCC');
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
