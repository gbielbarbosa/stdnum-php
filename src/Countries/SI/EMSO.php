<?php
namespace StdNum\Countries\SI;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class EMSO implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $weights = [7, 6, 5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $total = 0;
        for ($i = 0; $i < 12; $i++) {
            $total += $weights[$i] * (int)$number[$i];
        }
        
        $s = -$total % 11;
        if ($s < 0) {
            $s += 11;
        }
        
        return (string)($s % 10);
    }

    private function getBirthDate(string $number): ?\DateTime
    {
        $day = (int)substr($number, 0, 2);
        $month = (int)substr($number, 2, 2);
        $year = (int)substr($number, 4, 3);
        
        if ($year < 800) {
            $year += 2000;
        } else {
            $year += 1000;
        }

        if ($month === 0 || $day === 0 || !checkdate($month, $day, $year)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 13) {
            return ValidationResult::failure('Invalid length for EMSO');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for EMSO');
        }

        if ($this->getBirthDate($cleaned) === null) {
            return ValidationResult::failure('Invalid component for EMSO');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, -1)) !== substr($cleaned, -1)) {
            return ValidationResult::failure('Invalid checksum for EMSO');
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
        return trim(str_replace(' ', '', $number));
    }
}
