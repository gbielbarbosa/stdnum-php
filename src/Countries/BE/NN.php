<?php

namespace StdNum\Countries\BE;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use DateTime;

class NN implements DocumentInterface
{
    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 11) {
            return ValidationResult::failure('Invalid length for Belgian NN.');
        }

        if (!ctype_digit($compact) || (int)$compact <= 0) {
            return ValidationResult::failure('NN must contain only digits.');
        }

        $baseMonth = (int)substr($compact, 2, 2) % 20;
        
        if ($baseMonth > 12) {
            return ValidationResult::failure('Invalid month range for NN.');
        }

        if (!$this->checksumNN($compact)) {
            return ValidationResult::failure('Invalid checksum for NN.');
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
        if (strlen($compact) !== 11) {
            return $number;
        }

        return substr($compact, 0, 2) . '.' . substr($compact, 2, 2) . '.' . substr($compact, 4, 2) . '-' . substr($compact, 6, 3) . '.' . substr($compact, 9, 2);
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-', '.'], '', $number)));
    }

    protected function checksumNN(string $number): bool
    {
        $currentYear = (int)date('Y');
        
        $variants = [$number];
        if ((int)substr($number, 0, 2) + 2000 <= $currentYear) {
            $variants[] = '2' . $number;
        }

        $checkTarget = (int)substr($number, 9, 2);
        foreach ($variants as $n) {
            $base = (int)substr($n, 0, -2);
            if (97 - ($base % 97) === $checkTarget) {
                return true;
            }
        }

        return false;
    }
}
