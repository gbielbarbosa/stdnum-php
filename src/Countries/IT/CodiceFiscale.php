<?php
namespace StdNum\Countries\IT;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class CODICEFISCALE implements DocumentInterface
{
    use Cleanable;

    private $dateDigits = [
        '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4, 
        '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
        'L' => 0, 'M' => 1, 'N' => 2, 'P' => 3, 'Q' => 4, 
        'R' => 5, 'S' => 6, 'T' => 7, 'U' => 8, 'V' => 9,
    ];

    private $monthDigits = [
        'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'H' => 6, 
        'L' => 7, 'M' => 8, 'P' => 9, 'R' => 10, 'S' => 11, 'T' => 12,
    ];

    private $oddValues = [
        '0' => 1, '1' => 0, '2' => 5, '3' => 7, '4' => 9, '5' => 13, 
        '6' => 15, '7' => 17, '8' => 19, '9' => 21, 'A' => 1, 'B' => 0, 
        'C' => 5, 'D' => 7, 'E' => 9, 'F' => 13, 'G' => 15, 'H' => 17, 
        'I' => 19, 'J' => 21, 'K' => 2, 'L' => 4, 'M' => 18, 'N' => 20, 
        'O' => 11, 'P' => 3, 'Q' => 6, 'R' => 8, 'S' => 12, 'T' => 14, 
        'U' => 16, 'V' => 10, 'W' => 22, 'X' => 25, 'Y' => 24, 'Z' => 23,
    ];

    private $evenValues = [
        '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, 
        '6' => 6, '7' => 7, '8' => 8, '9' => 9, 'A' => 0, 'B' => 1, 
        'C' => 2, 'D' => 3, 'E' => 4, 'F' => 5, 'G' => 6, 'H' => 7, 
        'I' => 8, 'J' => 9, 'K' => 10, 'L' => 11, 'M' => 12, 'N' => 13, 
        'O' => 14, 'P' => 15, 'Q' => 16, 'R' => 17, 'S' => 18, 'T' => 19, 
        'U' => 20, 'V' => 21, 'W' => 22, 'X' => 23, 'Y' => 24, 'Z' => 25,
    ];

    private function calcCheckDigit(string $number): string
    {
        $code = 0;
        for ($i = 0; $i < 15; $i++) {
            $char = $number[$i];
            if ($i % 2 === 0) { // 0-based, so even index is odd position in 1-based
                $code += $this->oddValues[$char];
            } else {
                $code += $this->evenValues[$char];
            }
        }
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return $alphabet[$code % 26];
    }

    private function getBirthDate(string $number): ?\DateTime
    {
        $minyear = 1920;
        
        $d1 = $this->dateDigits[$number[9]] ?? null;
        $d2 = $this->dateDigits[$number[10]] ?? null;
        if ($d1 === null || $d2 === null) return null;
        
        $day = ($d1 * 10 + $d2) % 40;
        
        $month = $this->monthDigits[$number[8]] ?? null;
        if ($month === null) return null;
        
        $y1 = $this->dateDigits[$number[6]] ?? null;
        $y2 = $this->dateDigits[$number[7]] ?? null;
        if ($y1 === null || $y2 === null) return null;
        
        $year = $y1 * 10 + $y2;
        
        $year += floor($minyear / 100) * 100;
        if ($year < $minyear) {
            $year += 100;
        }

        if ($day === 0 || !checkdate($month, $day, $year)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) === 11) {
            $iva = new IVA();
            return $iva->validate($cleaned);
        }

        if (strlen($cleaned) !== 16) {
            return ValidationResult::failure('Invalid length for CODICEFISCALE');
        }

        if (!preg_match('/^[A-Z]{6}[0-9LMNPQRSTUV]{2}[ABCDEHLMPRST]{1}[0-9LMNPQRSTUV]{2}[A-Z]{1}[0-9LMNPQRSTUV]{3}[A-Z]{1}$/', $cleaned)) {
            return ValidationResult::failure('Invalid format for CODICEFISCALE');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, 15)) !== $cleaned[15]) {
            return ValidationResult::failure('Invalid checksum for CODICEFISCALE');
        }

        if ($this->getBirthDate($cleaned) === null) {
            return ValidationResult::failure('Invalid component for CODICEFISCALE');
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
        return trim(strtoupper(str_replace([' ', '-', ':'], '', $number)));
    }
}
