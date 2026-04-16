<?php

namespace StdNum\Countries\ES;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class NIF implements DocumentInterface
{
    protected DNI $dni;
    protected NIE $nie;
    protected CIF $cif;

    public function __construct()
    {
        $this->dni = new DNI();
        $this->nie = new NIE();
        $this->cif = new CIF();
    }

    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 9) {
            return ValidationResult::failure('Invalid length for a NIF number.');
        }

        if (!ctype_digit(substr($compact, 1, 7))) {
            return ValidationResult::failure('Invalid format for NIF.');
        }

        if (in_array($compact[0], ['K', 'L', 'M'])) {
            // Special rules, uses DNI check
            if ($compact[8] !== $this->dni->calcCheckDigit(substr($compact, 1, 7))) {
                return ValidationResult::failure('Invalid checksum for special NIF.');
            }
            return ValidationResult::success();
        } elseif (ctype_digit($compact[0])) {
            return $this->dni->validate($compact);
        } elseif (in_array($compact[0], ['X', 'Y', 'Z'])) {
            return $this->nie->validate($compact);
        }

        return $this->cif->validate($compact);
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $compact = $this->compact($number);
        if (ctype_digit($compact[0])) {
            return $this->dni->format($compact);
        } elseif (in_array($compact[0], ['X', 'Y', 'Z'])) {
            return $this->nie->format($compact);
        }

        return $this->cif->format($compact);
    }

    public function compact(string $number): string
    {
        $compact = trim(strtoupper(str_replace([' ', '-'], '', $number)));
        if (str_starts_with($compact, 'ES')) {
            $compact = substr($compact, 2);
        }
        
        // Let DNI take care of padding if length is smaller but ends with letter
        if (strlen($compact) < 9 && ctype_digit(substr($compact, 0, -1))) {
            $compact = str_pad(substr($compact, 0, -1), 8, '0', STR_PAD_LEFT) . substr($compact, -1);
        }
        
        return $compact;
    }
}

