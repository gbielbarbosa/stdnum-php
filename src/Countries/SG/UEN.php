<?php
namespace StdNum\Countries\SG;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class UEN implements DocumentInterface
{
    use Cleanable;

    private array $otherEntityTypes = [
        'CC', 'CD', 'CH', 'CL', 'CM', 'CP', 'CS', 'CX', 'DP', 'FB', 'FC', 'FM',
        'FN', 'GA', 'GB', 'GS', 'HS', 'LL', 'LP', 'MB', 'MC', 'MD', 'MH', 'MM',
        'MQ', 'NB', 'NR', 'PA', 'PB', 'PF', 'RF', 'RP', 'SM', 'SS', 'TC', 'TU',
        'VH', 'XL',
    ];

    private function calcBusinessCheckDigit(string $number): string
    {
        $weights = [10, 4, 9, 3, 8, 2, 7, 1];
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return 'XMKECAWLJDB'[$sum % 11];
    }

    private function validateBusiness(string $number): ValidationResult
    {
        if (!ctype_digit(substr($number, 0, -1))) {
            return ValidationResult::failure('Invalid format for UEN');
        }

        if (!ctype_alpha(substr($number, -1))) {
            return ValidationResult::failure('Invalid format for UEN');
        }

        if ($this->calcBusinessCheckDigit($number) !== substr($number, -1)) {
            return ValidationResult::failure('Invalid checksum for UEN');
        }

        return ValidationResult::success();
    }

    private function calcLocalCompanyCheckDigit(string $number): string
    {
        $weights = [10, 8, 6, 4, 9, 7, 5, 3, 1];
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $weights[$i] * (int)$number[$i];
        }
        return 'ZKCMDNERGWH'[$sum % 11];
    }

    private function validateLocalCompany(string $number): ValidationResult
    {
        if (!ctype_digit(substr($number, 0, -1))) {
            return ValidationResult::failure('Invalid format for UEN');
        }

        $currentYear = (int)date('Y');
        $year = (int)substr($number, 0, 4);

        if ($year > $currentYear) {
            return ValidationResult::failure('Invalid component for UEN');
        }

        if ($this->calcLocalCompanyCheckDigit($number) !== substr($number, -1)) {
            return ValidationResult::failure('Invalid checksum for UEN');
        }

        return ValidationResult::success();
    }

    private function calcOtherCheckDigit(string $number): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWX0123456789';
        $weights = [4, 3, 5, 3, 10, 2, 2, 5, 7];
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += strpos($alphabet, $number[$i]) * $weights[$i];
        }
        
        $idx = ($sum - 5) % 11;
        if ($idx < 0) {
            $idx += 11;
        }

        return $alphabet[$idx];
    }

    private function validateOther(string $number): ValidationResult
    {
        if (!in_array($number[0], ['R', 'S', 'T'], true)) {
            return ValidationResult::failure('Invalid component for UEN');
        }

        if (!ctype_digit(substr($number, 1, 2))) {
            return ValidationResult::failure('Invalid format for UEN');
        }

        $currentYearShort = substr(date('Y'), 2, 2);
        if ($number[0] === 'T' && substr($number, 1, 2) > $currentYearShort) {
            return ValidationResult::failure('Invalid component for UEN');
        }

        if (!in_array(substr($number, 3, 2), $this->otherEntityTypes, true)) {
            return ValidationResult::failure('Invalid component for UEN');
        }

        if (!ctype_digit(substr($number, 5, 4))) {
            return ValidationResult::failure('Invalid format for UEN');
        }

        if ($this->calcOtherCheckDigit($number) !== substr($number, -1)) {
            return ValidationResult::failure('Invalid checksum for UEN');
        }

        return ValidationResult::success();
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);
        $len = strlen($cleaned);

        if ($len !== 9 && $len !== 10) {
            return ValidationResult::failure('Invalid length for UEN');
        }

        if ($len === 9) {
            return $this->validateBusiness($cleaned);
        }

        if (ctype_digit($cleaned[0])) {
            return $this->validateLocalCompany($cleaned);
        }

        return $this->validateOther($cleaned);
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
        return trim(strtoupper(str_replace(' ', '', $number)));
    }
}
