<?php
namespace StdNum\Countries\US;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class ITIN implements DocumentInterface
{
    use Cleanable;

    public function validate(string $number): ValidationResult
    {
        $raw = trim(str_replace(' ', '', $number));

        if (!preg_match('/^([0-9]{3})-?([0-9]{2})-?([0-9]{4})$/', $raw, $matches)) {
            return ValidationResult::failure('Invalid format for ITIN');
        }

        $area = $matches[1];
        $group = (int)$matches[2];

        if ($area[0] !== '9') {
            return ValidationResult::failure('Invalid component for ITIN');
        }

        if ($group < 70 || $group > 99 || $group === 89 || $group === 93) {
            return ValidationResult::failure('Invalid component for ITIN');
        }

        return ValidationResult::success();
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $cleaned = $this->compact($number);
        if (strlen($cleaned) === 9) {
            return substr($cleaned, 0, 3) . '-' . substr($cleaned, 3, 2) . '-' . substr($cleaned, 5);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        return trim(str_replace('-', '', $number));
    }
}
