<?php

namespace StdNum\Countries\US;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class SSN implements DocumentInterface
{
    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 9) {
            return ValidationResult::failure('Invalid length for SSN.');
        }

        if (!ctype_digit($compact)) {
            return ValidationResult::failure('SSN must contain only digits.');
        }

        $area = substr($compact, 0, 3);
        $group = substr($compact, 3, 2);
        $serial = substr($compact, 5, 4);

        if ($area === '000' || $area === '666' || $area[0] === '9' || $group === '00' || $serial === '0000') {
            return ValidationResult::failure('Invalid SSN component. Area, group, or serial is invalid.');
        }

        $blacklist = ['078051120', '457555462', '219099999'];
        if (in_array($compact, $blacklist)) {
            return ValidationResult::failure('Blacklisted SSN.');
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

        return substr($compact, 0, 3) . '-' . substr($compact, 3, 2) . '-' . substr($compact, 5);
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}

