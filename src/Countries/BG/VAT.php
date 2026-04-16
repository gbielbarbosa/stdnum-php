<?php
namespace StdNum\Countries\BG;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class VAT implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigitLegal(string $number): string
    {
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += ($i + 1) * (int)$number[$i];
        }
        $check = $sum % 11;
        if ($check === 10) {
            $sum = 0;
            for ($i = 0; $i < 8; $i++) {
                $sum += ($i + 3) * (int)$number[$i];
            }
            $check = $sum % 11;
        }
        return (string)($check % 10);
    }

    private function calcCheckDigitOther(string $number): string
    {
        $weights = [4, 3, 2, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return (string)((11 - ($sum % 11)) % 11);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for Bulgarian VAT');
        }

        if (strlen($cleaned) === 9) {
            if ($cleaned[8] !== $this->calcCheckDigitLegal(substr($cleaned, 0, 8))) {
                return ValidationResult::failure('Invalid checksum for Bulgarian VAT');
            }
        } elseif (strlen($cleaned) === 10) {
            $egn = new EGN();
            $pnf = new PNF();
            
            if (!$egn->isValid($cleaned) && !$pnf->isValid($cleaned)) {
                if ($cleaned[9] !== $this->calcCheckDigitOther(substr($cleaned, 0, 9))) {
                    return ValidationResult::failure('Invalid checksum for Bulgarian VAT');
                }
            }
        } else {
            return ValidationResult::failure('Invalid length for Bulgarian VAT');
        }

        return ValidationResult::success();
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        return $this->compact($number);
    }

    public function compact(string $number): string
    {
        $number = trim(strtoupper(str_replace([' ', '-', '.'], '', $number)));
        if (str_starts_with($number, 'BG')) {
            return substr($number, 2);
        }
        return $number;
    }
}
