<?php
namespace StdNum\Countries\ZA;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;
use StdNum\Traits\LuhnChecksum;
use DateTime;

class ID implements DocumentInterface
{
    use Cleanable, LuhnChecksum;

    public function getBirthDate(string $number): ?DateTime
    {
        $cleaned = $this->compact($number);
        if (strlen($cleaned) < 6) return null;

        $today = new DateTime();
        $yearPart = (int)substr($cleaned, 0, 2);
        
        $currentYear = (int)$today->format('Y');
        $century = floor($currentYear / 100) * 100;
        
        $year = $yearPart + $century;
        if ($year > $currentYear) {
            $year -= 100;
        }

        $month = (int)substr($cleaned, 2, 2);
        $day = (int)substr($cleaned, 4, 2);

        if (!checkdate($month, $day, $year)) {
            return null;
        }

        return (new DateTime())->setDate($year, $month, $day)->setTime(0, 0, 0);
    }

    public function getGender(string $number): string
    {
        $cleaned = $this->compact($number);
        if (in_array($cleaned[6], ['0', '1', '2', '3', '4'], true)) {
            return 'F';
        }
        return 'M';
    }

    public function getCitizenship(string $number): string
    {
        $cleaned = $this->compact($number);
        if ($cleaned[10] === '0') {
            return 'citizen';
        } elseif ($cleaned[10] === '1') {
            return 'resident';
        }
        return 'unknown';
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for ID');
        }

        if (strlen($cleaned) !== 13) {
            return ValidationResult::failure('Invalid length for ID');
        }

        if ($this->getBirthDate($cleaned) === null) {
            return ValidationResult::failure('Invalid component for ID');
        }

        if ($this->getCitizenship($cleaned) === 'unknown') {
            return ValidationResult::failure('Invalid component for ID');
        }

        if (!$this->verifyLuhn($cleaned)) {
            return ValidationResult::failure('Invalid checksum for ID');
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
        if (strlen($cleaned) === 13) {
            return substr($cleaned, 0, 6) . ' ' . substr($cleaned, 6, 4) . ' ' . substr($cleaned, 10, 2) . ' ' . substr($cleaned, 12, 1);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        return trim(str_replace(' ', '', $number));
    }
}
