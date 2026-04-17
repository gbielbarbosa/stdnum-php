<?php
namespace StdNum\Countries\US;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class RTN implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $d = array_map('intval', str_split($number));
        $checksum = (
            7 * ($d[0] + $d[3] + $d[6]) +
            3 * ($d[1] + $d[4] + $d[7]) +
            9 * ($d[2] + $d[5])
        ) % 10;
        
        return (string)$checksum;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for RTN');
        }

        if (strlen($cleaned) !== 9) {
            return ValidationResult::failure('Invalid length for RTN');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, -1)) !== substr($cleaned, -1)) {
            return ValidationResult::failure('Invalid checksum for RTN');
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
