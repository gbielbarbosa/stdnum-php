<?php
namespace StdNum\Countries\CZ;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class DIC implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigitLegal(string $number): string
    {
        $sum = 0;
        for ($i = 0; $i < 7; $i++) {
            $sum += (8 - $i) * (int)$number[$i];
        }
        $check = (11 - ($sum % 11)) % 11;
        $check = ($check === 0) ? 1 : $check;
        return (string)($check % 10);
    }

    private function calcCheckDigitSpecial(string $number): string
    {
        $sum = 0;
        for ($i = 0; $i < 7; $i++) {
            $sum += (8 - $i) * (int)$number[$i];
        }
        $check = $sum % 11;
        
        $mod = (10 - $check) % 11;
        if ($mod < 0) {
            $mod += 11;
        }
        $val = (8 - $mod) % 10;
        if ($val < 0) {
            $val += 10;
        }
        return (string)$val;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for DIC');
        }

        $len = strlen($cleaned);

        if ($len === 8) {
            if (str_starts_with($cleaned, '9')) {
                return ValidationResult::failure('Invalid component for DIC');
            }
            if ($cleaned[7] !== $this->calcCheckDigitLegal(substr($cleaned, 0, 7))) {
                return ValidationResult::failure('Invalid checksum for DIC');
            }
        } elseif ($len === 9 && str_starts_with($cleaned, '6')) {
            if ($cleaned[8] !== $this->calcCheckDigitSpecial(substr($cleaned, 1, 7))) {
                return ValidationResult::failure('Invalid checksum for DIC');
            }
        } elseif ($len === 9 || $len === 10) {
            $rc = new RC();
            if (!$rc->isValid($cleaned)) {
                return ValidationResult::failure('Invalid component for DIC (invalid RC)');
            }
        } else {
            return ValidationResult::failure('Invalid length for DIC');
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
        $number = trim(strtoupper(str_replace([' ', '/'], '', $number)));
        if (str_starts_with($number, 'CZ')) {
            return substr($number, 2);
        }
        return $number;
    }
}
