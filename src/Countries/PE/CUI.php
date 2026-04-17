<?php
namespace StdNum\Countries\PE;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class CUI implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigits(string $number): string
    {
        $weights = [3, 2, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        $c = $sum % 11;
        $options1 = '65432110987';
        $options2 = 'KJIHGFEDCBA';
        return $options1[$c] . $options2[$c];
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        $len = strlen($cleaned);
        if ($len !== 8 && $len !== 9) {
            return ValidationResult::failure('Invalid length for CUI');
        }

        if (!ctype_digit(substr($cleaned, 0, 8))) {
            return ValidationResult::failure('Invalid format for CUI');
        }

        if ($len === 9) {
            $checkDigits = $this->calcCheckDigits(substr($cleaned, 0, 8));
            if (strpos($checkDigits, $cleaned[8]) === false) {
                return ValidationResult::failure('Invalid checksum for CUI');
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
