<?php
namespace StdNum\Countries\IE;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class VAT implements DocumentInterface
{
    use Cleanable;

    private $alphabet = 'WABCDEFGHIJKLMNOPQRSTUV';

    public function calcCheckDigit(string $number): string
    {
        $number = str_pad($number, 7, '0', STR_PAD_LEFT);
        
        $sum = 0;
        for ($i = 0; $i < 7; $i++) {
            $sum += (8 - $i) * (int)$number[$i];
        }
        
        if (strlen($number) > 7) {
            $sum += 9 * strpos($this->alphabet, substr($number, 7));
        }

        return $this->alphabet[$sum % 23];
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit(substr($cleaned, 0, 1)) || !ctype_digit(substr($cleaned, 2, 5))) {
            return ValidationResult::failure('Invalid format for VAT');
        }

        $rest = substr($cleaned, 7);
        for ($i = 0; $i < strlen($rest); $i++) {
            if (strpos($this->alphabet, $rest[$i]) === false) {
                return ValidationResult::failure('Invalid format for VAT');
            }
        }

        if (strlen($cleaned) !== 8 && strlen($cleaned) !== 9) {
            return ValidationResult::failure('Invalid length for VAT');
        }

        if (ctype_digit(substr($cleaned, 0, 7))) {
            if ($cleaned[7] !== $this->calcCheckDigit(substr($cleaned, 0, 7) . substr($cleaned, 8))) {
                return ValidationResult::failure('Invalid checksum for VAT');
            }
        } elseif (strpos('ABCDEFGHIJKLMNOPQRSTUVWXYZ+*', $cleaned[1]) !== false) {
            if ($cleaned[7] !== $this->calcCheckDigit(substr($cleaned, 2, 5) . $cleaned[0])) {
                return ValidationResult::failure('Invalid checksum for VAT');
            }
        } else {
            return ValidationResult::failure('Invalid format for VAT');
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
        $number = trim(strtoupper(str_replace([' ', '-'], '', $number)));
        if (str_starts_with($number, 'IE')) {
            return substr($number, 2);
        }
        return $number;
    }
}
