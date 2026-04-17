<?php
namespace StdNum\Countries\MX;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class CURP implements DocumentInterface
{
    use Cleanable;

    private array $nameBlacklist = [
        'BACA', 'BAKA', 'BUEI', 'BUEY', 'CACA', 'CACO', 'CAGA', 'CAGO', 'CAKA', 'CAKO', 'COGE', 'COGI', 'COJA', 'COJE',
        'COJI', 'COJO', 'COLA', 'CULO', 'FALO', 'FETO', 'GETA', 'GUEI', 'GUEY', 'JETA', 'JOTO', 'KACA', 'KACO', 'KAGA',
        'KAGO', 'KAKA', 'KAKO', 'KOGE', 'KOGI', 'KOJA', 'KOJE', 'KOJI', 'KOJO', 'KOLA', 'KULO', 'LILO', 'LOCA', 'LOCO',
        'LOKA', 'LOKO', 'MAME', 'MAMO', 'MEAR', 'MEAS', 'MEON', 'MIAR', 'MION', 'MOCO', 'MOKO', 'MULA', 'MULO', 'NACA',
        'NACO', 'PEDA', 'PEDO', 'PENE', 'PIPI', 'PITO', 'POPO', 'PUTA', 'PUTO', 'QULO', 'RATA', 'ROBA', 'ROBE', 'ROBO',
        'RUIN', 'SENO', 'TETA', 'VACA', 'VAGA', 'VAGO', 'VAKA', 'VUEI', 'VUEY', 'WUEI', 'WUEY'
    ];

    private array $validStates = [
        'AS', 'BC', 'BS', 'CC', 'CH', 'CL', 'CM', 'CS', 'DF', 'DG', 'GR', 'GT', 'HG', 'JC', 'MC', 'MN', 'MS', 'NE', 'NL', 'NT', 'OC', 'PL', 'QR', 'QT',
        'SL', 'SP', 'SR', 'TC', 'TL', 'TS', 'VZ', 'YN', 'ZS'
    ];

    private string $alphabet = '0123456789ABCDEFGHIJKLMN&OPQRSTUVWXYZ';

    private function getBirthDate(string $number): ?\DateTime
    {
        $year = (int)substr($number, 4, 2);
        $month = (int)substr($number, 6, 2);
        $day = (int)substr($number, 8, 2);

        if (ctype_digit($number[16])) {
            $year += 1900;
        } else {
            $year += 2000;
        }

        if ($month === 0 || $day === 0 || !checkdate($month, $day, $year)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    private function getGender(string $number): string
    {
        $gender = $number[10];
        if ($gender === 'H' || $gender === 'M') {
            return $gender;
        }
        return '';
    }

    private function calcCheckDigit(string $number): string
    {
        $check = 0;
        for ($i = 0; $i < 17; $i++) {
            $check += strpos($this->alphabet, $number[$i]) * (18 - $i);
        }
        return (string)((10 - $check % 10) % 10);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 18) {
            return ValidationResult::failure('Invalid length for CURP');
        }

        if (!preg_match('/^[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9A-Z][0-9]$/', $cleaned)) {
            return ValidationResult::failure('Invalid format for CURP');
        }

        if (in_array(substr($cleaned, 0, 4), $this->nameBlacklist, true)) {
            return ValidationResult::failure('Invalid component (blacklist) for CURP');
        }

        if ($this->getBirthDate($cleaned) === null) {
            return ValidationResult::failure('Invalid component (date) for CURP');
        }

        if ($this->getGender($cleaned) === '') {
            return ValidationResult::failure('Invalid component (gender) for CURP');
        }

        if (!in_array(substr($cleaned, 11, 2), $this->validStates, true)) {
            return ValidationResult::failure('Invalid component (state) for CURP');
        }

        if ($this->calcCheckDigit(substr($cleaned, 0, 17)) !== $cleaned[17]) {
            return ValidationResult::failure('Invalid checksum for CURP');
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
        return trim(strtoupper(str_replace(['-', '_', ' '], '', $number)));
    }
}
