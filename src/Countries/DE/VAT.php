<?php

namespace StdNum\Countries\DE;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class VAT implements DocumentInterface
{
    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 9) {
            return ValidationResult::failure('Invalid length for DE VAT number.');
        }

        if (!ctype_digit($compact) || $compact[0] === '0') {
            return ValidationResult::failure('Invalid format or starting digit for DE VAT.');
        }

        if ($this->checksum($compact) !== 1) {
            return ValidationResult::failure('Invalid checksum for DE VAT number.');
        }

        return ValidationResult::success();
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        // No specific complex format standard
        return $this->compact($number);
    }

    public function compact(string $number): string
    {
        $compact = trim(strtoupper(str_replace([' ', '-', '.', '/', ','], '', $number)));
        if (str_starts_with($compact, 'DE')) {
            $compact = substr($compact, 2);
        }
        return $compact;
    }

    protected function checksum(string $number): int
    {
        $check = 5;
        for ($i = 0; $i < strlen($number); $i++) {
            $c = $check === 0 ? 10 : $check;
            $c = ($c * 2) % 11;
            $check = ($c + (int)$number[$i]) % 10;
        }
        return $check;
    }
}

