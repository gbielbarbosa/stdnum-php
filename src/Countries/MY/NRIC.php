<?php
namespace StdNum\Countries\MY;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class NRIC implements DocumentInterface
{
    use Cleanable;

    private array $validPbCodes = [
        '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16',
        '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36',
        '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49', '50', '51', '52',
        '53', '54', '55', '56', '57', '58', '59',
        '60', '61', '62', '63', '64', '65', '66', '67', '68',
        '71', '72', '74', '75', '76', '77', '78', '79',
        '82', '83', '84', '85', '86', '87', '88', '89', '90', '91', '92', '93', '98', '99'
    ];

    private function getBirthDate(string $number): ?\DateTime
    {
        $year = (int)substr($number, 0, 2);
        $month = (int)substr($number, 2, 2);
        $day = (int)substr($number, 4, 2);

        // Try 1900
        $year1 = $year + 1900;
        if ($month > 0 && $day > 0 && checkdate($month, $day, $year1)) {
            return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year1, $month, $day));
        }

        // Try 2000
        $year2 = $year + 2000;
        if ($month > 0 && $day > 0 && checkdate($month, $day, $year2)) {
            return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year2, $month, $day));
        }

        return null;
    }

    private function isValidBirthPlace(string $number): bool
    {
        $pb = substr($number, 6, 2);
        return in_array($pb, $this->validPbCodes, true);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 12) {
            return ValidationResult::failure('Invalid length for NRIC');
        }

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for NRIC');
        }

        if ($this->getBirthDate($cleaned) === null) {
            return ValidationResult::failure('Invalid component (date) for NRIC');
        }

        if (!$this->isValidBirthPlace($cleaned)) {
            return ValidationResult::failure('Invalid component (birth place) for NRIC');
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
        if (strlen($cleaned) === 12) {
            return substr($cleaned, 0, 6) . '-' . substr($cleaned, 6, 2) . '-' . substr($cleaned, 8);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-', '*'], '', $number)));
    }
}
