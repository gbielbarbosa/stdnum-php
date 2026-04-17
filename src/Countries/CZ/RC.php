<?php
namespace StdNum\Countries\CZ;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class RC implements DocumentInterface
{
    use Cleanable;

    private function getBirthDate(string $number): ?\DateTime
    {
        $year = 1900 + (int)substr($number, 0, 2);
        $month = (int)substr($number, 2, 2) % 50 % 20;
        $day = (int)substr($number, 4, 2);

        if (strlen($number) === 9) {
            if ($year >= 1980) {
                $year -= 100;
            }
            if ($year > 1953) {
                return null; // Invalid Length
            }
        } elseif ($year < 1954) {
            $year += 100;
        }

        // Fix invalid day/month before checkdate
        if ($month === 0 || $day === 0) {
             return null;
        }
        
        if (!checkdate($month, $day, $year)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for RC');
        }

        if (strlen($cleaned) !== 9 && strlen($cleaned) !== 10) {
            return ValidationResult::failure('Invalid length for RC');
        }

        if ($this->getBirthDate($cleaned) === null) {
            return ValidationResult::failure('Invalid component for RC');
        }

        if (strlen($cleaned) === 10) {
            $check = ((int)substr($cleaned, 0, 9)) % 11 % 10;
            if ($cleaned[9] !== (string)$check) {
                return ValidationResult::failure('Invalid checksum for RC');
            }
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
        if (strlen($compact) >= 6) {
            return substr($compact, 0, 6) . '/' . substr($compact, 6);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '/'], '', $number)));
    }
}
