<?php
namespace StdNum\Countries\NZ;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class IRD implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $primaryWeights = [3, 2, 7, 6, 5, 4, 3, 2];
        $secondaryWeights = [7, 4, 3, 2, 5, 2, 7, 6];
        
        $number = str_pad($number, 8, '0', STR_PAD_LEFT);
        
        $sum1 = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum1 += $primaryWeights[$i] * (int)$number[$i];
        }
        $s = (-$sum1 % 11);
        if ($s < 0) {
            $s += 11;
        }
        
        if ($s !== 10) {
            return (string)$s;
        }

        $sum2 = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum2 += $secondaryWeights[$i] * (int)$number[$i];
        }
        $s2 = (-$sum2 % 11);
        if ($s2 < 0) {
            $s2 += 11;
        }
        
        return (string)$s2;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        $len = strlen($cleaned);
        if ($len !== 8 && $len !== 9) {
            return ValidationResult::failure('Invalid length for IRD');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for IRD');
        }

        $val = (int)$cleaned;
        if ($val <= 10000000 || $val >= 150000000) {
            return ValidationResult::failure('Invalid component for IRD');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, -1)) !== substr($cleaned, -1)) {
            return ValidationResult::failure('Invalid checksum for IRD');
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
        if (strlen($cleaned) >= 8) {
            return substr($cleaned, 0, -6) . '-' . substr($cleaned, -6, 3) . '-' . substr($cleaned, -3);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        $number = trim(strtoupper(str_replace([' ', '-'], '', $number)));
        if (str_starts_with($number, 'NZ')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
