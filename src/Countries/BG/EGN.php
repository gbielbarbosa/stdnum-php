<?php
namespace StdNum\Countries\BG;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class EGN implements DocumentInterface
{
    use Cleanable;

    private function getBirthDate(string $number): ?\DateTime
    {
        $year = (int)substr($number, 0, 2) + 1900;
        $month = (int)substr($number, 2, 2);
        $day = (int)substr($number, 4, 2);

        if ($month > 40) {
            $year += 100;
            $month -= 40;
        } elseif ($month > 20) {
            $year -= 100;
            $month -= 20;
        }

        if (!checkdate($month, $day, $year)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    private function calcCheckDigit(string $number): string
    {
        $weights = [2, 4, 8, 5, 10, 9, 7, 3, 6];
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return (string)($sum % 11 % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for EGN');
        }

        if (strlen($cleaned) !== 10) {
            return ValidationResult::failure('Invalid length for EGN');
        }

        if ($this->getBirthDate($cleaned) === null) {
            return ValidationResult::failure('Invalid component for EGN');
        }

        if ($cleaned[9] !== $this->calcCheckDigit($cleaned)) {
            return ValidationResult::failure('Invalid checksum for EGN');
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
        return trim(strtoupper(str_replace([' ', '-', '.'], '', $number)));
    }
}
