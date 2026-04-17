<?php
namespace StdNum\Countries\RS;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class PIB implements DocumentInterface
{
    use Cleanable;

    private function mod11_10(string $number): bool
    {
        $check = 5;
        for ($i = 0; $i < strlen($number); $i++) {
            $check = (($check === 0 ? 10 : $check) * 2) % 11 + (int)$number[$i];
            $check %= 10;
        }
        return $check === 1;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for PIB');
        }

        if (strlen($cleaned) !== 9) {
            return ValidationResult::failure('Invalid length for PIB');
        }

        if (!$this->mod11_10($cleaned)) {
            return ValidationResult::failure('Invalid checksum for PIB');
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
        return trim(str_replace([' ', '-', '.'], '', $number));
    }
}
