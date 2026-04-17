<?php
namespace StdNum\Countries\MC;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;
use StdNum\Countries\FR\TVA as FRTVA;

class TVA implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $frTva = new FRTVA();
        // Monaco TVA behaves like French TVA
        $cleaned = $this->compact($number);
        
        $result = $frTva->validate($cleaned);
        if (!$result->isValid) {
            return $result;
        }

        // Restrict Monaco specific positions: '000' at [2:5]
        if (substr($cleaned, 2, 3) !== '000') {
            return ValidationResult::failure('Invalid component for Monaco TVA');
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
        $frTva = new FRTVA();
        $compacted = $frTva->compact($number);
        return $compacted;
    }
}
