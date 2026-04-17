<?php
namespace StdNum\Countries\MX;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class RFC implements DocumentInterface
{
    use Cleanable;

    private array $nameBlacklist = [
        'BUEI', 'BUEY', 'CACA', 'CACO', 'CAGA', 'CAGO', 'CAKA', 'CAKO', 'COGE',
        'COJA', 'COJE', 'COJI', 'COJO', 'CULO', 'FETO', 'GUEY', 'JOTO', 'KACA',
        'KACO', 'KAGA', 'KAGO', 'KAKA', 'KOGE', 'KOJO', 'KULO', 'MAME', 'MAMO',
        'MEAR', 'MEAS', 'MEON', 'MION', 'MOCO', 'MULA', 'PEDA', 'PEDO', 'PENE',
        'PUTA', 'PUTO', 'QULO', 'RATA', 'RUIN',
    ];

    private string $alphabet = '0123456789ABCDEFGHIJKLMN&OPQRSTUVWXYZ Ñ';

    private function getDate(string $number): ?\DateTime
    {
        $year = (int)substr($number, 0, 2);
        $month = (int)substr($number, 2, 2);
        $day = (int)substr($number, 4, 2);

        $year += 2000;

        if ($month === 0 || $day === 0 || !checkdate($month, $day, $year)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);
        $len = mb_strlen($cleaned);

        if ($len === 10 || $len === 13) {
            // Personal number
            if (!preg_match('/^[A-Z&Ñ]{4}[0-9]{6}[0-9A-Z]{0,3}$/u', $cleaned)) {
                return ValidationResult::failure('Invalid format for RFC');
            }
            if (in_array(mb_substr($cleaned, 0, 4), $this->nameBlacklist, true)) {
                return ValidationResult::failure('Invalid component for RFC');
            }
            if ($this->getDate(mb_substr($cleaned, 4, 6)) === null) {
                return ValidationResult::failure('Invalid component for RFC');
            }
        } elseif ($len === 12) {
            // Company number
            if (!preg_match('/^[A-Z&Ñ]{3}[0-9]{6}[0-9A-Z]{3}$/u', $cleaned)) {
                return ValidationResult::failure('Invalid format for RFC');
            }
            if ($this->getDate(mb_substr($cleaned, 3, 6)) === null) {
                return ValidationResult::failure('Invalid component for RFC');
            }
        } else {
            return ValidationResult::failure('Invalid length for RFC');
        }

        // Check format of check digits if 12+ len
        if ($len >= 12) {
            $last3 = mb_substr($cleaned, -3);
            if (!preg_match('/^[1-9A-V][1-9A-Z][0-9A]$/u', $last3)) {
                return ValidationResult::failure('Invalid component (check format) for RFC');
            }
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
        $len = mb_strlen($cleaned);
        if ($len === 12) {
            return mb_substr($cleaned, 0, 3) . ' ' . mb_substr($cleaned, 3, 6) . ' ' . mb_substr($cleaned, 9);
        } elseif ($len >= 10) {
            return mb_substr($cleaned, 0, 4) . ' ' . mb_substr($cleaned, 4, 6) . ' ' . mb_substr($cleaned, 10);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        return trim(mb_strtoupper(str_replace(['-', '_', ' '], '', $number)));
    }
}
