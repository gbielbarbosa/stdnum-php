<?php
namespace StdNum\Countries\EC;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class CI implements DocumentInterface
{
    use Cleanable;

    private function checksum(string $number): int
    {
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $w = ($i % 2 === 0) ? 2 : 1;
            $x = $w * (int)$number[$i];
            if ($x > 9) {
                $x -= 9;
            }
            $sum += $x;
        }
        return $sum % 10;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 10) {
            return ValidationResult::failure('Invalid length for CI');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for CI');
        }

        $province = substr($cleaned, 0, 2);
        if (($province < '01' || $province > '24') && !in_array($province, ['30', '50'])) {
            return ValidationResult::failure('Invalid component for CI');
        }

        if ($cleaned[2] > '6') {
            return ValidationResult::failure('Invalid component for CI');
        }

        if ($this->checksum($cleaned) !== 0) {
            return ValidationResult::failure('Invalid checksum for CI');
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
