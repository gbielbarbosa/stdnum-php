<?php
namespace StdNum\Countries\NO;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;
use StdNum\Traits\LuhnChecksum;

class KontoNr implements DocumentInterface
{
    use Cleanable;
    use LuhnChecksum;

    private function calcCheckDigit(string $number): string
    {
        $weights = [6, 7, 8, 9, 4, 5, 6, 7, 8, 9];
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return (string)($sum % 11);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for KontoNr');
        }

        $len = strlen($cleaned);
        if ($len === 7) {
            if (!$this->verifyLuhn($cleaned)) {
                return ValidationResult::failure('Invalid checksum for KontoNr');
            }
        } elseif ($len === 11) {
            if ($this->calcCheckDigit($cleaned) !== $cleaned[10]) {
                return ValidationResult::failure('Invalid checksum for KontoNr');
            }
        } else {
            return ValidationResult::failure('Invalid length for KontoNr');
        }

        return ValidationResult::success();
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $cleaned = str_pad($this->compact($number), 11, '0', STR_PAD_LEFT);
        return substr($cleaned, 0, 4) . '.' . substr($cleaned, 4, 2) . '.' . substr($cleaned, 6);
    }

    public function compact(string $number): string
    {
        $number = trim(str_replace([' ', '.', '-'], '', $number));
        if (str_starts_with($number, '0000')) {
            $number = substr($number, 4);
        }
        return $number;
    }
}
