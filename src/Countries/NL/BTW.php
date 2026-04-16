<?php

namespace StdNum\Countries\NL;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Mod97_10;

class BTW implements DocumentInterface
{
    use Mod97_10;

    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 12) {
            return ValidationResult::failure('Invalid length for Dutch BTW number.');
        }

        $prefix = substr($compact, 0, 9);
        $suffix = substr($compact, 10, 2);

        if (!ctype_digit($prefix) || (int)$prefix <= 0) {
            return ValidationResult::failure('Invalid base layout for BTW.');
        }

        if (!ctype_digit($suffix) || (int)$suffix <= 0) {
            return ValidationResult::failure('Invalid suffix for BTW.');
        }

        if ($compact[9] !== 'B') {
            return ValidationResult::failure('BTW number must contain B at the 10th position.');
        }

        $bsnValidator = new BSN();
        if (!$bsnValidator->isValid($prefix) && !$this->verifyMod97_10('NL' . $compact)) {
            return ValidationResult::failure('Invalid checksum for Dutch BTW.');
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
        if (strlen($compact) !== 12) {
            return $number;
        }

        return substr($compact, 0, 9) . ' ' . substr($compact, 9);
    }

    public function compact(string $number): string
    {
        $compact = trim(strtoupper(str_replace([' ', '-', '.'], '', $number)));
        if (str_starts_with($compact, 'NL')) {
            $compact = substr($compact, 2);
        }
        
        if (strlen($compact) > 3) {
            $bsnBase = substr($compact, 0, -3);
            $suffix = substr($compact, -3);
            return (new BSN())->compact($bsnBase) . $suffix;
        }
        
        return $compact;
    }
}
