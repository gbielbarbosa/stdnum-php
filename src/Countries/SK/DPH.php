<?php
namespace StdNum\Countries\SK;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class DPH implements DocumentInterface
{
    use Cleanable;

    private function calcChecksum(string $number): int
    {
        return (int)$number % 11;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for DPH');
        }

        if (strlen($cleaned) !== 10) {
            return ValidationResult::failure('Invalid length for DPH');
        }

        $rc = new RC();
        if ($rc->isValid($cleaned)) {
            return ValidationResult::success();
        }

        if ($cleaned[0] === '0' || !in_array((int)$cleaned[2], [2, 3, 4, 7, 8, 9], true)) {
            return ValidationResult::failure('Invalid format for DPH');
        }

        if ($this->calcChecksum($cleaned) !== 0) {
            return ValidationResult::failure('Invalid checksum for DPH');
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
        $number = trim(strtoupper(str_replace([' ', '-'], '', $number)));
        if (str_starts_with($number, 'SK')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
