<?php
namespace StdNum\Countries\IS;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class KENNITALA implements DocumentInterface
{
    use Cleanable;

    private function calcCheckSum(string $number): int
    {
        $weights = [3, 2, 7, 6, 5, 4, 3, 2, 1, 0];
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return $sum % 11;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!preg_match('/^([01234567]\d)([01]\d)(\d\d)(\d\d)(\d)([09])$/', $cleaned, $matches)) {
            return ValidationResult::failure('Invalid format for KENNITALA');
        }

        $day = (int)$matches[1];
        $month = (int)$matches[2];
        $year = (int)$matches[3];
        $century = $matches[6] === '9' ? 1900 : 2000;
        $year += $century;

        try {
            if ($day >= 40) {
                if (!checkdate($month, $day - 40, $year)) {
                    return ValidationResult::failure('Invalid component for KENNITALA');
                }
            } else {
                if ($day === 0 || !checkdate($month, $day, $year)) {
                    return ValidationResult::failure('Invalid component for KENNITALA');
                }
            }
        } catch (\Exception $e) {
            return ValidationResult::failure('Invalid component for KENNITALA');
        }

        if ($this->calcCheckSum($cleaned) !== 0) {
            return ValidationResult::failure('Invalid checksum for KENNITALA');
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
        if (strlen($compact) === 10) {
            return substr($compact, 0, 6) . '-' . substr($compact, 6);
        }
        return $compact;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}
