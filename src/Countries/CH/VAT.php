<?php
namespace StdNum\Countries\CH;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class VAT implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 15 && strlen($cleaned) !== 16) {
            return ValidationResult::failure('Invalid length for Swiss VAT');
        }

        $uidBase = substr($cleaned, 0, 12);
        
        $uidValidator = new UID();
        $uidResult = $uidValidator->validate($uidBase);
        if (!$uidResult->isValid) {
            return $uidResult;
        }

        $suffix = substr($cleaned, 12);
        if (!in_array($suffix, ['MWST', 'TVA', 'IVA', 'TPV'])) {
            return ValidationResult::failure('Invalid component for Swiss VAT');
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
        if (strlen($compact) >= 15) {
            $uidValidator = new UID();
            return $uidValidator->format(substr($compact, 0, 12)) . ' ' . substr($compact, 12);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        $uidValidator = new UID();
        return $uidValidator->compact($number);
    }
}
