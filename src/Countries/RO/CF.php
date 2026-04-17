<?php
namespace StdNum\Countries\RO;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class CF implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) === 13) {
            $cnp = new CNP();
            return $cnp->validate($cleaned);
        } elseif (strlen($cleaned) >= 2 && strlen($cleaned) <= 10) {
            $cui = new CUI();
            return $cui->validate($cleaned);
        }

        return ValidationResult::failure('Invalid length for CF');
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
        $number = trim(strtoupper(str_replace([' ', '-'], '', $number)));
        if (str_starts_with($number, 'RO')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
