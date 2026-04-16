<?php

namespace StdNum\Countries\GB;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class NINO implements DocumentInterface
{
    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 9) {
            return ValidationResult::failure('Invalid length for a NINO number.');
        }

        // /^ (?!BG|GB|KN|NK|NT|TN|ZZ) [A-CEGHJ-PR-TW-Z] [A-CEGHJ-NPR-TW-Z] \d{6} [A-D] $/x
        $pattern = '/^(?!BG|GB|KN|NK|NT|TN|ZZ)[A-CEGHJ-PR-TW-Z][A-CEGHJ-NPR-TW-Z]\d{6}[A-D]$/i';
        
        if (!preg_match($pattern, $compact)) {
            return ValidationResult::failure('Invalid format for a NINO number.');
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

        return preg_replace('/^([A-Z]{2})(\d{2})(\d{2})(\d{2})([A-Z])$/i', '$1 $2 $3 $4 $5', $compact) ?? $number;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace(' ', '', $number)));
    }
}

