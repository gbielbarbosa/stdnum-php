<?php

namespace StdNum\Countries\BR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class CNPJ implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 14) {
            return ValidationResult::failure('Invalid length for a CNPJ number.');
        }

        if (preg_match('/^(\d)\1{13}$/', $compact)) {
            return ValidationResult::failure('CNPJ numbers cannot be sequence of same digits.');
        }

        if (!$this->verifyDigit($compact)) {
            return ValidationResult::failure('Invalid checksum for the CNPJ number.');
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
        if (strlen($compact) !== 14) {
            return $number;
        }

        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $compact) ?? $number;
    }

    public function compact(string $number): string
    {
        return $this->cleanDigits($number);
    }

    protected function verifyDigit(string $cnpj): bool
    {
        for ($t = 12; $t < 14; $t++) {
            for ($d = 0, $m = ($t - 7), $i = 0; $i < $t; $i++) {
                $d += $cnpj[$i] * $m;
                $m = ($m == 2 ? 9 : $m - 1);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cnpj[$i] != $d) {
                return false;
            }
        }
        return true;
    }
}

