<?php
namespace StdNum\Countries\RO;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class CNP implements DocumentInterface
{
    use Cleanable;

    private array $counties = [
        '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15',
        '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30',
        '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45',
        '46', '47', '48', '51', '52', '70', '80', '81', '82', '83'
    ];

    private function calcCheckDigit(string $number): string
    {
        $weights = [2, 7, 9, 1, 4, 6, 3, 5, 8, 2, 7, 9];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        $check = $sum % 11;
        return $check === 10 ? '1' : (string)$check;
    }

    private function getBirthDate(string $number): ?\DateTime
    {
        $centuries = [
            '1' => 1900, '2' => 1900, '3' => 1800, '4' => 1800, '5' => 2000, '6' => 2000,
        ];
        
        $century = $centuries[$number[0]] ?? 1900;
        $year = (int)substr($number, 1, 2) + $century;
        $month = (int)substr($number, 3, 2);
        $day = (int)substr($number, 5, 2);

        if ($month === 0 || $day === 0 || !checkdate($month, $day, $year)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for CNP');
        }

        if (strpos('123456789', $cleaned[0]) === false) {
            return ValidationResult::failure('Invalid component for CNP');
        }

        if (strlen($cleaned) !== 13) {
            return ValidationResult::failure('Invalid length for CNP');
        }

        if ($this->getBirthDate($cleaned) === null) {
            return ValidationResult::failure('Invalid component for CNP');
        }

        $county = substr($cleaned, 7, 2);
        if (!in_array($county, $this->counties, true)) {
            return ValidationResult::failure('Invalid component for CNP');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, 12)) !== $cleaned[12]) {
            return ValidationResult::failure('Invalid checksum for CNP');
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
        return trim(str_replace([' ', '-'], '', $number));
    }
}
