<?php
namespace StdNum\Countries\NO;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class Fodselsnummer implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit1(string $number): string
    {
        $weights = [3, 7, 6, 1, 8, 9, 4, 5, 2];
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return (string)((11 - $sum % 11) % 11);
    }

    private function calcCheckDigit2(string $number): string
    {
        $weights = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return (string)((11 - $sum % 11) % 11);
    }

    private function getBirthDate(string $number): ?\DateTime
    {
        $day = (int)substr($number, 0, 2);
        $month = (int)substr($number, 2, 2);
        $year = (int)substr($number, 4, 2);
        $individual = (int)substr($number, 6, 3);

        if ($day >= 80) {
            // FH number
            return null;
        }

        if ($day > 40) {
            $day -= 40;
        }

        if ($month > 40) {
            $month -= 40;
        }

        if ($individual < 500) {
            $year += 1900;
        } elseif ($individual < 750 && $year >= 54) {
            $year += 1800;
        } elseif ($individual < 1000 && $year < 40) {
            $year += 2000;
        } elseif ($individual >= 900 && $individual < 1000 && $year >= 40) {
            $year += 1900;
        } else {
            return null;
        }

        if ($month === 0 || $day === 0 || !checkdate($month, $day, $year)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 11) {
            return ValidationResult::failure('Invalid length for Fodselsnummer');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for Fodselsnummer');
        }

        if ($this->calcCheckDigit1($cleaned) !== $cleaned[9]) {
            return ValidationResult::failure('Invalid checksum for Fodselsnummer');
        }

        if ($this->calcCheckDigit2($cleaned) !== $cleaned[10]) {
            return ValidationResult::failure('Invalid checksum for Fodselsnummer');
        }

        $date = $this->getBirthDate($cleaned);
        // Valid FH-numbers fail getBirthDate, python raises a specific error but python stdnum skips the `> datetime.date.today()` if the date is invalid? 
        // Wait, Python stdnum raises InvalidComponent if birth date is invalid OR FH number. 
        // Let's mimic python: if date is null, return failure! But wait, does python validate FH numbers successfully?
        // Python code: `if get_birth_date(number) > datetime.date.today(): raise InvalidComponent()`
        // Since `get_birth_date` raises exception for FH number, validation fails for FH numbers in python! Wait, what?
        // Ah, `FH-number` raises InvalidComponent inside `get_birth_date()`. `validate()` calls `get_birth_date(number)`, so it bubbles up. So FH numbers fail validation if we consider InvalidComponent a validation failure. YES.
        
        if ($date === null) {
            return ValidationResult::failure('Invalid component for Fodselsnummer');
        }

        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        if ($date > $today) {
            return ValidationResult::failure('Invalid component (future date) for Fodselsnummer');
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
        if (strlen($cleaned) === 11) {
            return substr($cleaned, 0, 6) . ' ' . substr($cleaned, 6);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        return trim(str_replace([' ', '-', ':'], '', $number));
    }
}
