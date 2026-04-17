<?php
namespace StdNum\Countries\PT;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class CC implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $sum = 0;
        $rev = strrev($number);
        
        for ($i = 0; $i < strlen($rev); $i++) {
            $char = $rev[$i];
            $val = strpos($alphabet, $char);
            
            if ($i % 2 === 0) {
                $val *= 2;
                if ($val > 9) {
                    $val -= 9;
                }
            }
            $sum += $val;
        }
        
        return (string)((10 - $sum % 10) % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!preg_match('/^\d*[A-Z0-9]{2}\d$/', $cleaned)) {
            return ValidationResult::failure('Invalid format for CC');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, -1)) !== substr($cleaned, -1)) {
            return ValidationResult::failure('Invalid checksum for CC');
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
            return substr($cleaned, 0, -4) . ' ' . substr($cleaned, -4, 1) . ' ' . substr($cleaned, -3);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace(' ', '', $number)));
    }
}
