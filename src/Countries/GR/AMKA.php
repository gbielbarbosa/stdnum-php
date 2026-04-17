<?php
namespace StdNum\Countries\GR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class AMKA implements DocumentInterface
{
    use Cleanable;

    private function getBirthDate(string $number): ?\DateTime
    {
        $day = (int)substr($number, 0, 2);
        $month = (int)substr($number, 2, 2);
        $year = (int)substr($number, 4, 2) + 1900;

        if ($month === 0 || $day === 0) {
             return null;
        }

        if (!checkdate($month, $day, $year)) {
            $year += 100;
            if (!checkdate($month, $day, $year)) {
                return null;
            }
        }

        return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    private function luhnValidate(string $number): bool
    {
        $sum = 0;
        $alt = false;
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $n = (int)$number[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
            $alt = !$alt;
        }
        return ($sum % 10 === 0);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for AMKA');
        }

        if (strlen($cleaned) !== 11) {
            return ValidationResult::failure('Invalid length for AMKA');
        }

        if (!$this->luhnValidate($cleaned)) {
            return ValidationResult::failure('Invalid checksum for AMKA');
        }

        if ($this->getBirthDate($cleaned) === null) {
            return ValidationResult::failure('Invalid component for AMKA');
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
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}
