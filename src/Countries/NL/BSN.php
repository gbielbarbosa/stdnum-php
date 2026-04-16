<?php

namespace StdNum\Countries\NL;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class BSN implements DocumentInterface
{
    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 9) {
            return ValidationResult::failure('Invalid length for BSN.');
        }

        if (!ctype_digit($compact) || (int)$compact <= 0) {
            return ValidationResult::failure('BSN must contain only digits and be greater than 0.');
        }

        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += (int)$compact[$i] * (9 - $i);
        }
        
        $checksum = ($sum - (int)$compact[8]) % 11;

        if ($checksum !== 0) {
            return ValidationResult::failure('Invalid checksum for BSN.');
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

        return substr($compact, 0, 4) . '.' . substr($compact, 4, 2) . '.' . substr($compact, 6);
    }

    public function compact(string $number): string
    {
        return str_pad(trim(strtoupper(str_replace([' ', '-', '.'], '', $number))), 9, '0', STR_PAD_LEFT);
    }
}
