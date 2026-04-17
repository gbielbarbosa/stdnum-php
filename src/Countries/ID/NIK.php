<?php
namespace StdNum\Countries\ID;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class NIK implements DocumentInterface
{
    use Cleanable;

    private function getBirthDate(string $number): ?\DateTime
    {
        $day = (int)substr($number, 6, 2) % 40;
        $month = (int)substr($number, 8, 2);
        $year = (int)substr($number, 10, 2);

        if ($day === 0 || $month === 0) {
            return null;
        }

        if (checkdate($month, $day, $year + 1900)) {
            return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year + 1900, $month, $day));
        }

        if (checkdate($month, $day, $year + 2000)) {
            return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year + 2000, $month, $day));
        }

        return null;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for NIK');
        }

        if (strlen($cleaned) !== 16) {
            return ValidationResult::failure('Invalid length for NIK');
        }

        if ($this->getBirthDate($cleaned) === null) {
            return ValidationResult::failure('Invalid component for NIK');
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
