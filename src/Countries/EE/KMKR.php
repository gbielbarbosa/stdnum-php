<?php
namespace StdNum\Countries\EE;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class KMKR implements DocumentInterface
{
    use Cleanable;

    private function checksum(string $number): int
    {
        $weights = [3, 7, 1, 3, 7, 1, 3, 7, 1];
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return $sum % 10;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for KMKR');
        }

        if (strlen($cleaned) !== 9) {
            return ValidationResult::failure('Invalid length for KMKR');
        }

        if ($this->checksum($cleaned) !== 0) {
            return ValidationResult::failure('Invalid checksum for KMKR');
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
        $number = trim(strtoupper(str_replace(' ', '', $number)));
        if (str_starts_with($number, 'EE')) {
            return substr($number, 2);
        }
        return $number;
    }
}
