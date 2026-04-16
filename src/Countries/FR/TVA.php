<?php

namespace StdNum\Countries\FR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class TVA implements DocumentInterface
{
    private SIREN $sirenValidator;
    private string $alphabet = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    public function __construct()
    {
        $this->sirenValidator = new SIREN();
    }

    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== 11) {
            return ValidationResult::failure('Invalid length for TVA number.');
        }

        if (strpos($this->alphabet, $compact[0]) === false || strpos($this->alphabet, $compact[1]) === false) {
            return ValidationResult::failure('Invalid format for TVA.');
        }

        if (!ctype_digit(substr($compact, 2))) {
            return ValidationResult::failure('Invalid format for TVA base.');
        }

        if (substr($compact, 2, 3) !== '000') {
            $sirenResult = $this->sirenValidator->validate(substr($compact, 2));
            if (!$sirenResult->isValid) {
                return ValidationResult::failure('Invalid SIREN base in TVA.');
            }
        }

        if (ctype_digit($compact)) {
            // all-numeric digits
            $sirenInt = bcmod(bcmul(substr($compact, 2), '100') . '12', '97'); // this handles int overflows, but standard int is enough since it's 11 digits: max ~999,999,999 * 100 which fits in 64 bit PHP easily.
            $expectedStr = substr($compact, 2) . '12';
            $modulo = intval($expectedStr) % 97;

            if (intval(substr($compact, 0, 2)) !== $modulo) {
                return ValidationResult::failure('Invalid numeric checksum for TVA.');
            }
        } else {
            // one of the first two digits isn't a number
            $isFirstDigit = ctype_digit($compact[0]);
            
            $check = 0;
            if ($isFirstDigit) {
                $check = (strpos($this->alphabet, $compact[0]) * 24) + strpos($this->alphabet, $compact[1]) - 10;
            } else {
                $check = (strpos($this->alphabet, $compact[0]) * 34) + strpos($this->alphabet, $compact[1]) - 100;
            }
            
            $sirenPart = intval(substr($compact, 2));
            
            if (($sirenPart + 1 + intdiv($check, 11)) % 11 !== ($check % 11)) {
                return ValidationResult::failure('Invalid alphanumeric checksum for TVA.');
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
        if (strlen($compact) !== 11) {
            return $number;
        }

        return 'FR ' . substr($compact, 0, 2) . ' ' . substr($compact, 2, 3) . ' ' . substr($compact, 5, 3) . ' ' . substr($compact, 8);
    }

    public function compact(string $number): string
    {
        $compact = trim(strtoupper(str_replace([' ', '-', '.'], '', $number)));
        if (str_starts_with($compact, 'FR')) {
            $compact = substr($compact, 2);
        }
        return $compact;
    }
}

