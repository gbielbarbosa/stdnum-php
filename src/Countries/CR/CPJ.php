<?php
namespace StdNum\Countries\CR;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class CPJ implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 10) {
            return ValidationResult::failure('Invalid length for CPJ');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for CPJ');
        }

        if (!in_array($cleaned[0], ['2', '3', '4', '5'])) {
            return ValidationResult::failure('Invalid component for CPJ');
        }

        $type = substr($cleaned, 1, 3);
        
        if ($cleaned[0] === '2' && !in_array($type, ['100', '200', '300', '400'])) {
            return ValidationResult::failure('Invalid component for CPJ');
        }

        $classThreeTypes = [
            '002', '003', '004', '005', '006', '007', '008',
            '009', '010', '011', '012', '013', '014', '101',
            '102', '103', '104', '105', '106', '107', '108',
            '109', '110'
        ];
        
        if ($cleaned[0] === '3' && !in_array($type, $classThreeTypes)) {
            return ValidationResult::failure('Invalid component for CPJ');
        }

        if ($cleaned[0] === '4' && $type !== '000') {
            return ValidationResult::failure('Invalid component for CPJ');
        }

        if ($cleaned[0] === '5' && $type !== '001') {
            return ValidationResult::failure('Invalid component for CPJ');
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
            return $compact[0] . '-' . substr($compact, 1, 3) . '-' . substr($compact, 4);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}
