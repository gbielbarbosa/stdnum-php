<?php
namespace StdNum\Countries\RU;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class INN implements DocumentInterface
{
    use Cleanable;

    private function calcCompanyCheckDigit(string $number): string
    {
        $weights = [2, 4, 10, 3, 5, 9, 4, 6, 8];
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return (string)(($sum % 11) % 10);
    }

    private function calcPersonalCheckDigits(string $number): string
    {
        $weights1 = [7, 2, 4, 10, 3, 5, 9, 4, 6, 8];
        $sum1 = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum1 += $weights1[$i] * (int)$number[$i];
        }
        $d1 = (string)(($sum1 % 11) % 10);

        $weights2 = [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8];
        $num2 = substr($number, 0, 10) . $d1;
        $sum2 = 0;
        for ($i = 0; $i < 11; $i++) {
            $sum2 += $weights2[$i] * (int)$num2[$i];
        }
        $d2 = (string)(($sum2 % 11) % 10);

        return $d1 . $d2;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for INN');
        }

        $len = strlen($cleaned);
        if ($len === 10) {
            if ($this->calcCompanyCheckDigit(substr($cleaned, 0, 9)) !== $cleaned[9]) {
                return ValidationResult::failure('Invalid checksum for INN');
            }
        } elseif ($len === 12) {
            if ($this->calcPersonalCheckDigits(substr($cleaned, 0, 10)) !== substr($cleaned, 10, 2)) {
                return ValidationResult::failure('Invalid checksum for INN');
            }
        } else {
            return ValidationResult::failure('Invalid length for INN');
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
        return trim(str_replace(' ', '', $number));
    }
}
