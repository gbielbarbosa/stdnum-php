<?php
namespace StdNum\Countries\DK;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class CPR implements DocumentInterface
{
    use Cleanable;

    private function getBirthDate(string $number): ?\DateTime
    {
        $day = (int)substr($number, 0, 2);
        $month = (int)substr($number, 2, 2);
        $year = (int)substr($number, 4, 2);
        $centuryMarker = $number[6];

        if (in_array($centuryMarker, ['5', '6', '7', '8']) && $year >= 58) {
            $year += 1800;
        } elseif (in_array($centuryMarker, ['0', '1', '2', '3']) || (in_array($centuryMarker, ['4', '9']) && $year >= 37)) {
            $year += 1900;
        } else {
            $year += 2000;
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
            return ValidationResult::failure('Invalid format for CPR');
        }

        if (strlen($cleaned) !== 10) {
            return ValidationResult::failure('Invalid length for CPR');
        }

        $birthDate = $this->getBirthDate($cleaned);
        if ($birthDate === null) {
            return ValidationResult::failure('Invalid component for CPR');
        }
        
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $birthDate->setTime(0, 0, 0);
        
        if ($birthDate > $today) {
            return ValidationResult::failure('Invalid component for CPR');
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
        if (strlen($compact) === 10) {
            return substr($compact, 0, 6) . '-' . substr($compact, 6);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}
