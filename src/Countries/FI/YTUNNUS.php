<?php
namespace StdNum\Countries\FI;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class YTUNNUS implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $alv = new ALV();
        return $alv->validate($number);
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $compact = $this->compact($number);
        if (strlen($compact) >= 8) {
            return substr($compact, 0, 7) . '-' . substr($compact, 7);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        $alv = new ALV();
        return $alv->compact($number);
    }
}
