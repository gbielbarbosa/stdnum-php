<?php
namespace StdNum\Countries\IL;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class IDNR implements DocumentInterface
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

        if (strlen($cleaned) > 9) {
            return ValidationResult::failure('Invalid length for IDNR');
        }

        if (!ctype_digit($cleaned) || (int)$cleaned <= 0) {
            return ValidationResult::failure('Invalid format for IDNR');
        }

        if (!$this->luhnValidate($cleaned)) {
            return ValidationResult::failure('Invalid checksum for IDNR');
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
        if (strlen($compact) === 9) {
            return substr($compact, 0, 8) . '-' . substr($compact, 8, 1);
        }
        return $compact;
    }

    public function compact(string $number): string
    {
        $number = trim(strtoupper(str_replace([' ', '-'], '', $number)));
        return str_pad($number, 9, '0', STR_PAD_LEFT);
    }
}
