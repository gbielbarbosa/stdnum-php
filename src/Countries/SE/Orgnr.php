<?php
namespace StdNum\Countries\SE;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;
use StdNum\Traits\LuhnChecksum;

class Orgnr implements DocumentInterface
{
    use Cleanable, LuhnChecksum;

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for Orgnr');
        }

        if (strlen($cleaned) !== 10) {
            return ValidationResult::failure('Invalid length for Orgnr');
        }

        if (!$this->verifyLuhn($cleaned)) {
            return ValidationResult::failure('Invalid checksum for Orgnr');
        }

        return ValidationResult::success();
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $cleaned = $this->compact($number);
        if (strlen($cleaned) === 10) {
            return substr($cleaned, 0, 6) . '-' . substr($cleaned, 6);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        return trim(str_replace([' ', '-', '.'], '', $number));
    }
}
