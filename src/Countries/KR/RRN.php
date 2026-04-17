<?php
namespace StdNum\Countries\KR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class RRN implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $weights = [2, 3, 4, 5, 6, 7, 8, 9, 2, 3, 4, 5];
        $check = 0;
        for ($i = 0; $i < 12; $i++) {
            $check += $weights[$i] * (int)$number[$i];
        }
        return (string)((11 - ($check % 11)) % 10);
    }

    private function getBirthDate(string $number): ?\DateTime
    {
        $year = (int)substr($number, 0, 2);
        $month = (int)substr($number, 2, 2);
        $day = (int)substr($number, 4, 2);
        $centuryChar = $number[6];

        if (in_array($centuryChar, ['1', '2', '5', '6'])) {
            $year += 1900;
        } elseif (in_array($centuryChar, ['3', '4', '7', '8'])) {
            $year += 2000;
        } else {
            $year += 1800;
        }

        if ($month === 0 || $day === 0 || !checkdate($month, $day, $year)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for RRN');
        }

        if (strlen($cleaned) !== 13) {
            return ValidationResult::failure('Invalid length for RRN');
        }

        if ($this->getBirthDate($cleaned) === null) {
            return ValidationResult::failure('Invalid component for RRN');
        }

        $placeOfBirth = (int)substr($cleaned, 7, 2);
        if ($placeOfBirth > 96) {
            return ValidationResult::failure('Invalid component for RRN');
        }

        if ($cleaned[12] !== $this->calcCheckDigit(substr($cleaned, 0, 12))) {
            return ValidationResult::failure('Invalid checksum for RRN');
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
        if (strlen($compact) === 13) {
            return substr($compact, 0, 6) . '-' . substr($compact, 6);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}
