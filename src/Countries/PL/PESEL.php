<?php
namespace StdNum\Countries\PL;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class PESEL implements DocumentInterface
{
    use Cleanable;

    private function getBirthDate(string $number): ?\DateTime
    {
        $year = (int)substr($number, 0, 2);
        $month = (int)substr($number, 2, 2);
        $day = (int)substr($number, 4, 2);

        $centuries = [
            0 => 1900,
            1 => 2000,
            2 => 2100,
            3 => 2200,
            4 => 1800,
        ];

        $centuryKey = (int)floor($month / 20);
        if (!isset($centuries[$centuryKey])) {
            return null;
        }

        $year += $centuries[$centuryKey];
        $month = $month % 20;

        if ($month === 0 || $day === 0 || !checkdate($month, $day, $year)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    private function calcCheckDigit(string $number): string
    {
        $weights = [1, 3, 7, 9, 1, 3, 7, 9, 1, 3];
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return (string)((10 - $sum % 10) % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for PESEL');
        }

        if (strlen($cleaned) !== 11) {
            return ValidationResult::failure('Invalid length for PESEL');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, 10)) !== $cleaned[10]) {
            return ValidationResult::failure('Invalid checksum for PESEL');
        }

        if ($this->getBirthDate($cleaned) === null) {
            return ValidationResult::failure('Invalid component for PESEL');
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
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}
