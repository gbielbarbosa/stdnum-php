<?php
namespace StdNum\Countries\BY;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class UNP implements DocumentInterface
{
    use Cleanable;

    private $cyrillicToLatinMap = [
        'А' => 'A', 'В' => 'B', 'Е' => 'E', 'К' => 'K',
        'М' => 'M', 'Н' => 'H', 'О' => 'O', 'Р' => 'P',
        'С' => 'C', 'Т' => 'T'
    ];

    private function calcCheckDigit(string $number): string
    {
        $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $weights = [29, 23, 19, 17, 13, 7, 5, 3];
        
        $target = $number;
        if (!ctype_digit($target)) {
            $mappedIdx = strpos('ABCEHKMOPT', $target[1]);
            if ($mappedIdx !== false) {
                $target = $target[0] . $mappedIdx . substr($target, 2);
            }
        }
        
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += $weights[$i] * strpos($alphabet, $target[$i]);
        }
        
        $c = $sum % 11;
        if ($c > 9) {
            return 'Invalid'; 
        }
        return (string)$c;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 9) {
            return ValidationResult::failure('Invalid length for UNP');
        }

        if (!ctype_digit(substr($cleaned, 2))) {
            return ValidationResult::failure('Invalid format for UNP');
        }

        $prefix = substr($cleaned, 0, 2);
        if (!ctype_digit($prefix)) {
            $allValidChars = true;
            for ($i = 0; $i < 2; $i++) {
                if (strpos('ABCEHKMOPT', $prefix[$i]) === false) {
                    $allValidChars = false;
                    break;
                }
            }
            if (!$allValidChars) {
                return ValidationResult::failure('Invalid format for UNP');
            }
        }

        if (strpos('1234567ABCEHKM', $cleaned[0]) === false) {
            return ValidationResult::failure('Invalid component for UNP');
        }

        $checkDigit = $this->calcCheckDigit($cleaned);
        if ($cleaned[8] !== $checkDigit) {
            return ValidationResult::failure('Invalid checksum for UNP');
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
        $number = trim(mb_strtoupper(str_replace(' ', '', $number)));
        
        // Remove prefixes
        if (mb_strpos($number, 'УНП') === 0) {
            $number = mb_substr($number, 3);
        } elseif (str_starts_with($number, 'UNP')) {
            $number = substr($number, 3);
        }
        
        $mapped = '';
        for ($i = 0; $i < mb_strlen($number); $i++) {
            $char = mb_substr($number, $i, 1);
            if (isset($this->cyrillicToLatinMap[$char])) {
                $mapped .= $this->cyrillicToLatinMap[$char];
            } else {
                $mapped .= $char;
            }
        }
        
        return $mapped;
    }
}
