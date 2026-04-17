<?php
namespace StdNum\Countries\RO;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class ONRC implements DocumentInterface
{
    use Cleanable;

    private array $counties = [
        1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 
        21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 
        39, 40, 51, 52
    ];

    private function calcCheckDigit(string $number): string
    {
        $numStr = (string)(ord($number[0]) % 10) . substr($number, 1);
        $sum = 0;
        for ($i = 0; $i < strlen($numStr) - 1; $i++) {
            $sum += (int)$numStr[$i];
        }
        return (string)($sum % 10);
    }

    private function validateOldFormat(string $number): ValidationResult
    {
        if (!preg_match('/^[A-Z][0-9]+\/[0-9]+\/[0-9]+$/', $number)) {
            return ValidationResult::failure('Invalid format for ONRC');
        }

        $parts = explode('/', substr($number, 1));
        $county = $parts[0];
        $serial = $parts[1];
        $year = $parts[2];

        if (strlen($serial) > 5) {
            return ValidationResult::failure('Invalid length for ONRC');
        }

        if (strlen($county) < 1 || strlen($county) > 2 || !in_array((int)$county, $this->counties, true)) {
            return ValidationResult::failure('Invalid component for ONRC');
        }

        if (strlen($year) !== 4) {
            return ValidationResult::failure('Invalid length for ONRC');
        }

        $y = (int)$year;
        if ($y < 1990 || $y > 2024) {
            return ValidationResult::failure('Invalid component for ONRC');
        }

        return ValidationResult::success();
    }

    private function validateNewFormat(string $number): ValidationResult
    {
        if (!ctype_digit(substr($number, 1))) {
            return ValidationResult::failure('Invalid format for ONRC');
        }

        if (strlen($number) !== 14) {
            return ValidationResult::failure('Invalid length for ONRC');
        }

        $year = (int)substr($number, 1, 4);
        $currentYear = (int)date('Y');
        
        if ($year < 1990 || $year > $currentYear) {
            return ValidationResult::failure('Invalid component for ONRC');
        }

        $county = (int)substr($number, 11, 2);
        
        if ($year < 2024) {
            if (!in_array($county, $this->counties, true)) {
                return ValidationResult::failure('Invalid component for ONRC');
            }
        } elseif ($year === 2024) {
            if ($county !== 0 && !in_array($county, $this->counties, true)) {
                return ValidationResult::failure('Invalid component for ONRC');
            }
        } else {
            if ($county !== 0) {
                return ValidationResult::failure('Invalid component for ONRC');
            }
        }

        if ($this->calcCheckDigit($number) !== substr($number, -1)) {
            return ValidationResult::failure('Invalid checksum for ONRC');
        }

        return ValidationResult::success();
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!in_array($cleaned[0] ?? '', ['J', 'F', 'C'], true)) {
            return ValidationResult::failure('Invalid component for ONRC');
        }

        if (strpos($cleaned, '/') !== false) {
            return $this->validateOldFormat($cleaned);
        } else {
            return $this->validateNewFormat($cleaned);
        }
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
        $number = preg_replace('/[ \/\\\\-]+/', '/', strtoupper(trim($number)));
        if (isset($number[1]) && $number[1] === '/') {
            $number = substr($number, 0, 1) . substr($number, 2);
        }
        if (isset($number[2]) && $number[2] === '/') {
            $number = substr($number, 0, 1) . '0' . substr($number, 1);
        }
        
        if (preg_match('/^([A-Z][0-9]+\/[0-9]+\/)\d{2}\.\d{2}\.(\d{4})$/', $number, $matches)) {
            $number = $matches[1] . $matches[2];
        }
        
        return $number;
    }
}
