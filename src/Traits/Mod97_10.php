<?php

namespace StdNum\Traits;

trait Mod97_10
{
    /**
     * Verifies the ISO 7064 Mod 97, 10 algorithm.
     */
    protected function verifyMod97_10(string $number): bool
    {
        $mapped = '';
        for ($i = 0; $i < strlen($number); $i++) {
            $c = strtoupper($number[$i]);
            if (ctype_alpha($c)) {
                $mapped .= (string)(ord($c) - 55); // 'A' in ASCII is 65. 65 - 55 = 10.
            } else {
                $mapped .= $c;
            }
        }

        // Calculate modulo using standard blocks to avoid needing bcmath extension
        $mod = 0;
        for ($i = 0; $i < strlen($mapped); $i++) {
            $mod = ($mod * 10 + (int)$mapped[$i]) % 97;
        }

        return $mod === 1;
    }
}
