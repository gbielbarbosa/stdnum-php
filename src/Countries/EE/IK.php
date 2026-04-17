<?php
namespace StdNum\Countries\EE;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class IK implements DocumentInterface
{
    use Cleanable;

    private function getBirthDate(string $number): ?\DateTime
    {
        $char = $number[0];
        if (in_array($char, ['1', '2'])) {
            $century = 1800;
        } elseif (in_array($char, ['3', '4'])) {
            $century = 1900;
        } elseif (in_array($char, ['5', '6'])) {
            $century = 2000;
        } elseif (in_array($char, ['7', '8'])) {
            $century = 2100;
        } else {
            return null;
        }

        $year = $century + (int)substr($number, 1, 2);
        $month = (int)substr($number, 3, 2);
        $day = (int)substr($number, 5, 2);

        if ($month === 0 || $day === 0 || !checkdate($month, $day, $year)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    public function calcCheckDigit(string $number): string
    {
        $len = strlen($number) - 1;
        $sum = 0;
        for ($i = 0; $i < $len; $i++) {
            $sum += (($i % 9) + 1) * (int)$number[$i];
        }
        $check = $sum % 11;
        
        if ($check === 10) {
            $sum = 0;
            for ($i = 0; $i < $len; $i++) {
                $sum += ((($i + 2) % 9) + 1) * (int)$number[$i];
            }
            $check = $sum % 11;
        }
        
        return (string)($check % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for IK');
        }

        if (strlen($cleaned) !== 11) {
            return ValidationResult::failure('Invalid length for IK');
        }

        if ($this->getBirthDate($cleaned) === null) {
            return ValidationResult::failure('Invalid component for IK');
        }

        if ($cleaned[10] !== $this->calcCheckDigit($cleaned)) {
            return ValidationResult::failure('Invalid checksum for IK');
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
