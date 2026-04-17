<?php
namespace StdNum\Countries\SE;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;
use StdNum\Traits\LuhnChecksum;

class Personnummer implements DocumentInterface
{
    use Cleanable, LuhnChecksum;

    private function getBirthDate(string $number): ?\DateTime
    {
        $cleaned = $this->compact($number);
        
        if (strlen($cleaned) === 13 || strlen($cleaned) === 12) { // the compact method returns length 12
            $year = (int)substr($cleaned, 0, 4);
            $month = (int)substr($cleaned, 4, 2);
            $day = (int)substr($cleaned, 6, 2);
        } else {
            return null; // internal error since we always normalize to 12
        }

        if (!checkdate($month, $day, $year)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    public function validate(string $number): ValidationResult
    {
        $compacted = $this->compactAndNormalize($number);
        
        if ($compacted === null) {
            return ValidationResult::failure('Invalid format or length for Personnummer');
        }

        // $compacted is always 12 digits from compactAndNormalize, but we need the 10 digits for checksum
        $digits = substr($compacted, 2); // get the YYMMDDXXXX part
        
        if (!ctype_digit($digits)) {
            return ValidationResult::failure('Invalid format for Personnummer');
        }

        if ($this->getBirthDate($compacted) === null) {
            return ValidationResult::failure('Invalid component for Personnummer');
        }

        if (!$this->verifyLuhn($digits)) {
            return ValidationResult::failure('Invalid checksum for Personnummer');
        }

        return ValidationResult::success();
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $cleaned = str_replace([' ', ':'], '', $number);
        if (in_array(strlen($cleaned), [10, 12]) && !in_array($cleaned[strlen($cleaned) - 5], ['-', '+'])) {
            $cleaned = substr($cleaned, 0, -4) . '-' . substr($cleaned, -4);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        return $this->compactAndNormalize($number) ?? trim(str_replace([' ', ':', '-', '+'], '', $number));
    }

    private function compactAndNormalize(string $number): ?string
    {
        $cleaned = str_replace([' ', ':'], '', $number);
        $len = strlen($cleaned);

        if ($len === 10 || $len === 11) {
            $separator = '-';
            if ($len === 11) {
                $separator = $cleaned[6];
                if ($separator !== '-' && $separator !== '+') {
                    return null;
                }
                $digits = substr($cleaned, 0, 6) . substr($cleaned, 7);
            } else {
                $digits = $cleaned;
            }

            if (!ctype_digit($digits)) {
                return null;
            }

            $currentYear = (int)date('Y');
            $century = (int)floor($currentYear / 100);
            $yy = (int)substr($digits, 0, 2);

            if ($yy > ($currentYear % 100)) {
                $century--;
            }
            if ($separator === '+') {
                $century--;
            }

            return sprintf('%02d%s', $century, $digits);
        }

        if ($len === 12 || $len === 13) {
            if ($len === 13) {
                $separator = $cleaned[8];
                if ($separator !== '-' && $separator !== '+') {
                    return null;
                }
                $digits = substr($cleaned, 0, 8) . substr($cleaned, 9);
            } else {
                $digits = $cleaned;
            }

            if (!ctype_digit($digits)) {
                return null;
            }

            return $digits;
        }

        return null;
    }
}
