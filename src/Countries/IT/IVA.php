<?php
namespace StdNum\Countries\IT;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class IVA implements DocumentInterface
{
    use Cleanable;

    private function luhnValidate(string $number): bool
    {
        $sum = 0;
        $alt = false;
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $n = (int)$number[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
            $alt = !$alt;
        }
        return ($sum % 10 === 0);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned) || substr($cleaned, 0, 7) === '0000000') {
            return ValidationResult::failure('Invalid format for IVA');
        }

        if (strlen($cleaned) !== 11) {
            return ValidationResult::failure('Invalid length for IVA');
        }

        $province = substr($cleaned, 7, 3);
        $validProvince = false;
        if ((int)$province >= 1 && (int)$province <= 100) {
            $validProvince = true;
        } elseif (in_array($province, ['120', '121', '888', '999'])) {
            $validProvince = true;
        }

        if (!$validProvince) {
            return ValidationResult::failure('Invalid component for IVA');
        }

        if (!$this->luhnValidate($cleaned)) {
            return ValidationResult::failure('Invalid checksum for IVA');
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
        $number = trim(strtoupper(str_replace([' ', '-', ':'], '', $number)));
        if (str_starts_with($number, 'IT')) {
            $number = substr($number, 2);
        }
        return $number;
    }
}
