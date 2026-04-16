<?php

namespace StdNum\Traits;

trait LuhnChecksum
{
    /**
     * Calculates the Luhn checksum for the given number.
     * Returns true if valid, false otherwise.
     */
    protected function verifyLuhn(string $number): bool
    {
        $sum = 0;
        $length = strlen($number);
        $isSecond = false;
        
        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int)$number[$i];
            
            if ($isSecond) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
            $isSecond = !$isSecond;
        }

        return ($sum % 10) === 0;
    }
}
