<?php

namespace StdNum\Countries\BR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class CPF implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 11) {
            return ValidationResult::failure('Invalid length for a CPF number.');
        }

        if (preg_match('/^(\d)\1{10}$/', $compact)) {
            return ValidationResult::failure('CPF numbers cannot be sequence of same digits.');
        }

        if (!$this->verifyDigit($compact)) {
            return ValidationResult::failure('Invalid checksum for the CPF number.');
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
        if (strlen($compact) !== 11) {
            return $number;
        }

        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $compact) ?? $number;
    }

    public function compact(string $number): string
    {
        return $this->cleanDigits($number);
    }

    protected function verifyDigit(string $cpf): bool
    {
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }
}

