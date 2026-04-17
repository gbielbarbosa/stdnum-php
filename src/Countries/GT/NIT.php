<?php
namespace StdNum\Countries\GT;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class NIT implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $sum = 0;
        $len = strlen($number);
        for ($i = 0; $i < $len; $i++) {
            $n = (int)$number[$len - 1 - $i];
            $sum += ($i + 2) * $n;
        }
        
        $c = (-$sum % 11);
        if ($c < 0) {
            $c += 11;
        }
        
        return $c === 10 ? 'K' : (string)$c;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) < 2 || strlen($cleaned) > 12) {
            return ValidationResult::failure('Invalid length for NIT');
        }

        $base = substr($cleaned, 0, -1);
        $check = substr($cleaned, -1);

        if (!ctype_digit($base)) {
            return ValidationResult::failure('Invalid format for NIT');
        }

        if ($check !== 'K' && !ctype_digit($check)) {
            return ValidationResult::failure('Invalid format for NIT');
        }

        if ($check !== $this->calcCheckDigit($base)) {
            return ValidationResult::failure('Invalid checksum for NIT');
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
        if (strlen($compact) >= 2) {
            return substr($compact, 0, -1) . '-' . substr($compact, -1);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        $number = trim(strtoupper(str_replace([' ', '-'], '', $number)));
        return ltrim($number, '0');
    }
}
