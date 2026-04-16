<?php

namespace StdNum\Countries\CA;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\LuhnChecksum;

class BN implements DocumentInterface
{
    use LuhnChecksum;

    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);
        $len = strlen($compact);

        if ($len !== 9 && $len !== 15) {
            return ValidationResult::failure('Invalid length for BN.');
        }

        $base = substr($compact, 0, 9);
        if (!ctype_digit($base)) {
            return ValidationResult::failure('BN base must contain only digits.');
        }

        if (!$this->verifyLuhn($base)) {
            return ValidationResult::failure('Invalid checksum for BN base.');
        }

        if ($len === 15) {
            $program = substr($compact, 9, 2);
            if (!in_array($program, ['RC', 'RM', 'RP', 'RT'])) {
                return ValidationResult::failure('Invalid program identifier for BN15.');
            }

            if (!ctype_digit(substr($compact, 11))) {
                return ValidationResult::failure('Invalid reference number formatting for BN15.');
            }
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
        if (strlen($compact) === 15) {
            return substr($compact, 0, 9) . ' ' . substr($compact, 9, 2) . ' ' . substr($compact, 11);
        }
        return $compact;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}

