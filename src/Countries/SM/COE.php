<?php
namespace StdNum\Countries\SM;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class COE implements DocumentInterface
{
    use Cleanable;

    private array $lowNumbers = [
        2, 4, 6, 7, 8, 9, 10, 11, 13, 16, 18, 19, 20, 21, 25, 26, 30, 32, 33, 35,
        36, 37, 38, 39, 40, 42, 45, 47, 49, 51, 52, 55, 56, 57, 58, 59, 61, 62,
        64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 79, 80, 81, 84, 85,
        87, 88, 91, 92, 94, 95, 96, 97, 99
    ];

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) > 5 || strlen($cleaned) === 0) {
            return ValidationResult::failure('Invalid length for COE');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for COE');
        }

        if (strlen($cleaned) < 3 && !in_array((int)$cleaned, $this->lowNumbers, true)) {
            return ValidationResult::failure('Invalid component for COE');
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
        return ltrim(trim(str_replace('.', '', $number)), '0');
    }
}
