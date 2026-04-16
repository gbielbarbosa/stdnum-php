<?php
namespace StdNum\Countries\CR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class CPF implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 10) {
            return ValidationResult::failure('Invalid length for CPF');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for CPF');
        }

        if ($cleaned[0] !== '0') {
            return ValidationResult::failure('Invalid component for CPF');
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
            return substr($compact, 0, 2) . '-' . substr($compact, 2, 4) . '-' . substr($compact, 6);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        $number = trim(strtoupper(str_replace(' ', '', $number)));
        $parts = explode('-', $number);
        if (count($parts) === 3) {
            $parts[0] = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            $parts[1] = str_pad($parts[1], 4, '0', STR_PAD_LEFT);
            $parts[2] = str_pad($parts[2], 4, '0', STR_PAD_LEFT);
            $number = implode('', $parts);
        }
        if (strlen($number) === 9) {
            $number = '0' . $number;
        }
        return $number;
    }
}
