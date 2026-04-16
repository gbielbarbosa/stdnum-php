<?php

namespace StdNum\Countries\US;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class EIN implements DocumentInterface
{
    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 9) {
            return ValidationResult::failure('Invalid length for EIN.');
        }

        if (!ctype_digit($compact)) {
            return ValidationResult::failure('EIN must contain only digits.');
        }

        $prefix = substr($compact, 0, 2);
        $invalidPrefixes = ['00', '07', '08', '09', '17', '18', '19', '28', '29', '49', '69', '70', '78', '79', '89', '96', '97', '98', '99'];
        
        // This is a simplified check based on typically unused ranges,
        // python-stdnum has a specific white-list instead. We will flag invalid common prefixes.
        if (in_array($prefix, $invalidPrefixes)) {
            return ValidationResult::failure('Invalid EIN prefix.');
        }

        return ValidationResult::success();
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $compact = $this->compact($number);
        if (strlen($compact) !== 9) {
            return $number;
        }

        return substr($compact, 0, 2) . '-' . substr($compact, 2);
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}

