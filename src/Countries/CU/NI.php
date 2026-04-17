<?php
namespace StdNum\Countries\CU;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class NI implements DocumentInterface
{
    use Cleanable;

    private function getBirthDate(string $number): ?\DateTime
    {
        $year = (int)substr($number, 0, 2);
        $month = (int)substr($number, 2, 2);
        $day = (int)substr($number, 4, 2);

        if ($number[6] === '9') {
            $year += 1800;
        } elseif ($number[6] >= '0' && $number[6] <= '5') {
            $year += 1900;
        } else {
            $year += 2000;
        }

        if (!checkdate($month, $day, $year)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 11) {
            return ValidationResult::failure('Invalid length for NI');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for NI');
        }

        if ($this->getBirthDate($cleaned) === null) {
            return ValidationResult::failure('Invalid component for NI');
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
