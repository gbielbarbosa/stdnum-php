<?php
namespace StdNum\Countries\ID;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class NPWP implements DocumentInterface
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

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for NPWP');
        }

        if (strlen($cleaned) === 15) {
            if (!$this->luhnValidate(substr($cleaned, 0, 9))) {
                return ValidationResult::failure('Invalid checksum for NPWP');
            }
            return ValidationResult::success();
        }

        if (strlen($cleaned) === 16) {
            if (!str_starts_with($cleaned, '0')) {
                $nik = new NIK();
                return $nik->validate($cleaned);
            }
            
            if (!$this->luhnValidate(substr($cleaned, 0, 10))) {
                return ValidationResult::failure('Invalid checksum for NPWP');
            }
            return ValidationResult::success();
        }

        return ValidationResult::failure('Invalid length for NPWP');
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $compact = $this->compact($number);
        if (strlen($compact) === 15) {
            return substr($compact, 0, 2) . '.' . substr($compact, 2, 3) . '.' . substr($compact, 5, 3) . '.' . substr($compact, 8, 1) . '-' . substr($compact, 9, 3) . '.' . substr($compact, 12, 3);
        }
        return $compact;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-', '.'], '', $number)));
    }
}
