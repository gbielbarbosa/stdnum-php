<?php
namespace StdNum\Countries\TR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class TCKimlik implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigits(string $number): string
    {
        $sum1 = 0;
        $sum2 = 0;
        
        for ($i = 0; $i < 9; $i++) {
            $n = (int)$number[$i];
            $sum1 += ($i % 2 === 0 ? 3 : 1) * $n;
            $sum2 += $n;
        }

        $check1 = (10 - ($sum1 % 10)) % 10;
        $check2 = ($check1 + $sum2) % 10;
        
        return sprintf('%d%d', $check1, $check2);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned) || str_starts_with($cleaned, '0')) {
            return ValidationResult::failure('Invalid format for TCKimlik');
        }

        if (strlen($cleaned) !== 11) {
            return ValidationResult::failure('Invalid length for TCKimlik');
        }

        if ($this->calcCheckDigits($cleaned) !== substr($cleaned, -2)) {
            return ValidationResult::failure('Invalid checksum for TCKimlik');
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
