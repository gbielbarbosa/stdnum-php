<?php
namespace StdNum\Countries\US;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class SSN implements DocumentInterface
{
    use Cleanable;

    private array $blacklist = ['078-05-1120', '457-55-5462', '219-09-9999'];

    public function validate(string $number): ValidationResult
    {
        $raw = trim(str_replace(' ', '', $number));

        if (!preg_match('/^([0-9]{3})-?([0-9]{2})-?([0-9]{4})$/', $raw, $matches)) {
            return ValidationResult::failure('Invalid format for SSN');
        }

        $area = $matches[1];
        $group = $matches[2];
        $serial = $matches[3];

        if ($area === '000' || $area === '666' || $area[0] === '9' || $group === '00' || $serial === '0000') {
            return ValidationResult::failure('Invalid component for SSN');
        }

        if (in_array($this->format($number), $this->blacklist, true)) {
            return ValidationResult::failure('Invalid component for SSN');
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
