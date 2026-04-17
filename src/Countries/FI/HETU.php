<?php
namespace StdNum\Countries\FI;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class HETU implements DocumentInterface
{
    use Cleanable;

    private $centuryCodes = [
        '+' => 1800,
        '-' => 1900, 'Y' => 1900, 'X' => 1900, 'W' => 1900, 'V' => 1900, 'U' => 1900,
        'A' => 2000, 'B' => 2000, 'C' => 2000, 'D' => 2000, 'E' => 2000, 'F' => 2000,
    ];

    private function calcChecksum(string $number): string
    {
        $chars = '0123456789ABCDEFHJKLMNPRSTUVWXY';
        return $chars[(int)$number % 31];
    }

    public function validate(string $number, bool $allowTemporary = false): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!preg_match('/^([0123]\d)([01]\d)(\d\d)([\-\+ABCDEFYXWVU])(\d\d\d)([0-9ABCDEFHJKLMNPRSTUVWXY])$/', $cleaned, $matches)) {
            return ValidationResult::failure('Invalid format for HETU');
        }

        $day = (int)$matches[1];
        $month = (int)$matches[2];
        $year = (int)$matches[3];
        $centuryMarker = $matches[4];
        $individual = (int)$matches[5];
        $control = $matches[6];

        $century = $this->centuryCodes[$centuryMarker];

        if ($month === 0 || $day === 0 || !checkdate($month, $day, $century + $year)) {
            return ValidationResult::failure('Invalid component for HETU');
        }

        if ($individual < 2) {
            return ValidationResult::failure('Invalid component for HETU');
        }

        if ($individual >= 900 && $individual <= 999 && !$allowTemporary) {
            return ValidationResult::failure('Invalid component for HETU');
        }

        $checkableNumber = sprintf('%02d%02d%02d%03d', $day, $month, $year, $individual);
        if ($control !== $this->calcChecksum($checkableNumber)) {
            return ValidationResult::failure('Invalid checksum for HETU');
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
        return trim(strtoupper(str_replace([' '], '', $number)));
    }
}
