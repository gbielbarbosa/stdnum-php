<?php
namespace StdNum\Countries\EC;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class RUC implements DocumentInterface
{
    use Cleanable;

    private function checksum(string $number, array $weights): int
    {
        $sum = 0;
        for ($i = 0; $i < count($weights); $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return $sum % 11;
    }

    private function validateNatural(string $number): bool
    {
        if (substr($number, -3) === '000') {
            return false;
        }
        $ci = new CI();
        return $ci->isValid(substr($number, 0, 10));
    }

    private function validatePublic(string $number): bool
    {
        if (substr($number, -4) === '0000') {
            return false;
        }
        if ($this->checksum(substr($number, 0, 9), [3, 2, 7, 6, 5, 4, 3, 2, 1]) !== 0) {
            return false;
        }
        return true;
    }

    private function validateJuridical(string $number): bool
    {
        if (substr($number, -3) === '000') {
            return false;
        }
        if ($this->checksum(substr($number, 0, 10), [4, 3, 2, 7, 6, 5, 4, 3, 2, 1]) !== 0) {
            return false;
        }
        return true;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 13) {
            return ValidationResult::failure('Invalid length for RUC');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for RUC');
        }

        $province = substr($cleaned, 0, 2);
        if (($province < '01' || $province > '24') && !in_array($province, ['30', '50'])) {
            return ValidationResult::failure('Invalid component for RUC');
        }

        $valid = false;
        if ($cleaned[2] < '6') {
            $valid = $this->validateNatural($cleaned);
        } elseif ($cleaned[2] === '6') {
            $valid = $this->validatePublic($cleaned) || $this->validateNatural($cleaned);
        } elseif ($cleaned[2] === '9') {
            $valid = $this->validatePublic($cleaned) || $this->validateJuridical($cleaned);
        } else {
            return ValidationResult::failure('Invalid component for RUC');
        }

        if (!$valid) {
            return ValidationResult::failure('Invalid checksum or component for RUC');
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
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}
