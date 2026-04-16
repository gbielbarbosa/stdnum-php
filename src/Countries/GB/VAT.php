<?php

namespace StdNum\Countries\GB;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class VAT implements DocumentInterface
{
    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);
        $len = strlen($compact);

        if ($len === 5) {
            if (!ctype_digit(substr($compact, 2))) {
                return ValidationResult::failure('Invalid format for Government or Health VAT number.');
            }
            if (str_starts_with($compact, 'GD') && (int)substr($compact, 2) < 500) {
                // valid GD
            } elseif (str_starts_with($compact, 'HA') && (int)substr($compact, 2) >= 500) {
                // valid HA
            } else {
                return ValidationResult::failure('Invalid component for GD or HA.');
            }
            return ValidationResult::success();
        }

        if ($len === 11 && in_array(substr($compact, 0, 6), ['GD8888', 'HA8888'])) {
            if (!ctype_digit(substr($compact, 6))) {
                return ValidationResult::failure('Invalid format.');
            }
            $part = (int)substr($compact, 6, 3);
            if (str_starts_with($compact, 'GD') && $part < 500) {
                // valid
            } elseif (str_starts_with($compact, 'HA') && $part >= 500) {
                // valid
            } else {
                return ValidationResult::failure('Invalid component.');
            }

            if ($part % 97 !== (int)substr($compact, 9, 2)) {
                return ValidationResult::failure('Invalid checksum.');
            }

            return ValidationResult::success();
        }

        if (in_array($len, [9, 12])) {
            if (!ctype_digit($compact)) {
                return ValidationResult::failure('Standard VAT must contain only digits.');
            }
            $nineDigits = substr($compact, 0, 9);
            $checksumResult = $this->checksum($nineDigits);

            if ((int)substr($compact, 0, 3) >= 100) {
                if (!in_array($checksumResult, [0, 42, 55])) {
                    return ValidationResult::failure('Invalid checksum.');
                }
            } else {
                if ($checksumResult !== 0 && $checksumResult !== 55) {
                    // original python stdnum also tests 55 for legacy? the py says `not in (0, 42, 55)` above 100
                    // if under 100, `!= 0`, however for safety we will follow python-stdnum exactly
                    if ($checksumResult !== 0) {
                        return ValidationResult::failure('Invalid checksum.');
                    }
                }
            }

            return ValidationResult::success();
        }

        return ValidationResult::failure('Invalid length for a VAT number.');
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $compact = $this->compact($number);
        $len = strlen($compact);
        
        if ($len === 5) {
            return $compact;
        }

        if ($len === 12) {
            return substr($compact, 0, 3) . ' ' . substr($compact, 3, 4) . ' ' . substr($compact, 7, 2) . ' ' . substr($compact, 9);
        }

        if ($len === 9) {
            return substr($compact, 0, 3) . ' ' . substr($compact, 3, 4) . ' ' . substr($compact, 7);
        }

        return $number;
    }

    public function compact(string $number): string
    {
        $compact = trim(strtoupper(preg_replace('/[\s\-\.]/', '', $number)));
        if (str_starts_with($compact, 'GB') || str_starts_with($compact, 'XI')) {
            $compact = substr($compact, 2);
        }
        return $compact;
    }

    protected function checksum(string $number): int
    {
        $weights = [8, 7, 6, 5, 4, 3, 2, 10, 1];
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int)$number[$i] * $weights[$i];
        }
        return $sum % 97;
    }
}

