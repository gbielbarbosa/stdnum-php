<?php
namespace StdNum\Countries\IL;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class HP implements DocumentInterface
{
    use Cleanable;

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

        if (!ctype_digit($cleaned) || (int)$cleaned <= 0) {
            return ValidationResult::failure('Invalid format for HP');
        }

        if (strlen($cleaned) !== 9) {
            return ValidationResult::failure('Invalid length for HP');
        }

        if ($cleaned[0] !== '5') {
            return ValidationResult::failure('Invalid component for HP');
        }

        if (!$this->luhnValidate($cleaned)) {
            return ValidationResult::failure('Invalid checksum for HP');
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
