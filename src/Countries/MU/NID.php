<?php
namespace StdNum\Countries\MU;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class NID implements DocumentInterface
{
    use Cleanable;

    private string $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    private function calcCheckDigit(string $number): string
    {
        $check = 0;
        for ($i = 0; $i < 13; $i++) {
            $check += (14 - $i) * strpos($this->alphabet, $number[$i]);
        }
        $index = (17 - $check % 17) % 17;
        return $this->alphabet[$index];
    }

    private function getBirthDate(string $number): ?\DateTime
    {
        $day = (int)substr($number, 1, 2);
        $month = (int)substr($number, 3, 2);
        $year = (int)substr($number, 5, 2);

        $year += 2000;

        if ($month === 0 || $day === 0 || !checkdate($month, $day, $year)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 14) {
            return ValidationResult::failure('Invalid length for NID');
        }

        if (!preg_match('/^[A-Z][0-9]+[0-9A-Z]$/', $cleaned)) {
            return ValidationResult::failure('Invalid format for NID');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, 13)) !== $cleaned[13]) {
            return ValidationResult::failure('Invalid checksum for NID');
        }

        if ($this->getBirthDate($cleaned) === null) {
            return ValidationResult::failure('Invalid component for NID');
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
        return trim(strtoupper(str_replace([' '], '', $number)));
    }
}
