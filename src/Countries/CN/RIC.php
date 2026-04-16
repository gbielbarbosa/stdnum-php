<?php
namespace StdNum\Countries\CN;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class RIC implements DocumentInterface
{
    use Cleanable;

    private function getBirthDate(string $number): ?\DateTime
    {
        $year = (int)substr($number, 6, 4);
        $month = (int)substr($number, 10, 2);
        $day = (int)substr($number, 12, 2);

        if (!checkdate($month, $day, $year)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    private function calcCheckDigit(string $number): string
    {
        $base = substr($number, 0, 17);
        $weights = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        $sum = 0;
        for ($i = 0; $i < 17; $i++) {
            $sum += $weights[$i] * (int)$base[$i];
        }
        $map = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        return $map[$sum % 11];
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 18) {
            return ValidationResult::failure('Invalid length for RIC');
        }

        if (!ctype_digit(substr($cleaned, 0, 17))) {
            return ValidationResult::failure('Invalid format for RIC');
        }

        if ($cleaned[17] !== $this->calcCheckDigit($cleaned)) {
            return ValidationResult::failure('Invalid checksum for RIC');
        }

        if ($this->getBirthDate($cleaned) === null) {
            return ValidationResult::failure('Invalid component for RIC');
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
        return trim(strtoupper(str_replace(' ', '', $number)));
    }
}
