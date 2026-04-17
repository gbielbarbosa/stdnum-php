<?php
namespace StdNum\Countries\SE;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class VAT implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned) || !str_ends_with($cleaned, '01')) {
            return ValidationResult::failure('Invalid format for VAT');
        }

        if (strlen($cleaned) !== 12) {
            return ValidationResult::failure('Invalid length for VAT');
        }

        $orgnr = new Orgnr();
        return $orgnr->validate(substr($cleaned, 0, 10));
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
        $number = trim(strtoupper(str_replace([' ', '-', '.'], '', $number)));
        if (str_starts_with($number, 'SE')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
