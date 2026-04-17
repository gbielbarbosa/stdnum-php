<?php
namespace StdNum\Countries\NO;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class MVA implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!str_ends_with($cleaned, 'MVA')) {
            return ValidationResult::failure('Invalid format for MVA');
        }

        $orgnr = substr($cleaned, 0, -3);
        $orgnrValidator = new OrgNr();
        return $orgnrValidator->validate($orgnr);
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $cleaned = $this->compact($number);
        if (strlen($cleaned) >= 12 && str_ends_with($cleaned, 'MVA')) {
            $orgnrValidator = new OrgNr();
            return 'NO ' . $orgnrValidator->format(substr($cleaned, 0, 9)) . ' ' . substr($cleaned, 9);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        $number = trim(strtoupper(str_replace(' ', '', $number)));
        if (str_starts_with($number, 'NO')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
