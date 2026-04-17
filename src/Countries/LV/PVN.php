<?php
namespace StdNum\Countries\LV;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class PVN implements DocumentInterface
{
    use Cleanable;

    private function checksumLegal(string $number): int
    {
        $weights = [9, 1, 4, 8, 3, 10, 2, 5, 7, 6, 1];
        $sum = 0;
        for ($i = 0; $i < 11; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return $sum % 11;
    }

    private function calcCheckDigitPers(string $number): string
    {
        $weights = [10, 5, 8, 4, 2, 1, 6, 3, 7, 9];
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        $check = 1 + $sum;
        return (string)(($check % 11) % 10);
    }

    private function getBirthDate(string $number): ?\DateTime
    {
        $day = (int)substr($number, 0, 2);
        $month = (int)substr($number, 2, 2);
        $year = (int)substr($number, 4, 2);
        
        $centuryMarker = (int)$number[6];
        $year += 1800 + ($centuryMarker * 100);

        if ($month === 0 || $day === 0 || !checkdate($month, $day, $year)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for PVN');
        }

        if (strlen($cleaned) !== 11) {
            return ValidationResult::failure('Invalid length for PVN');
        }

        if ($cleaned[0] > '3') {
            // Legal entity
            if ($this->checksumLegal($cleaned) !== 3) {
                return ValidationResult::failure('Invalid checksum for PVN');
            }
        } elseif (str_starts_with($cleaned, '32')) {
            // Personal code without birth date
            if ($this->calcCheckDigitPers(substr($cleaned, 0, 10)) !== $cleaned[10]) {
                return ValidationResult::failure('Invalid checksum for PVN');
            }
        } else {
            // Natural resident
            if ($this->getBirthDate($cleaned) === null) {
                return ValidationResult::failure('Invalid component for PVN');
            }
            if ($this->calcCheckDigitPers(substr($cleaned, 0, 10)) !== $cleaned[10]) {
                return ValidationResult::failure('Invalid checksum for PVN');
            }
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
        $number = trim(strtoupper(str_replace([' ', '-'], '', $number)));
        if (str_starts_with($number, 'LV')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
