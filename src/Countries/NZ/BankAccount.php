<?php
namespace StdNum\Countries\NZ;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class BankAccount implements DocumentInterface
{
    use Cleanable;

    private array $algorithms = [
        '01' => 'A', '02' => 'A', '03' => 'A', '04' => 'A', '06' => 'A', '08' => 'D',
        '09' => 'E', '10' => 'A', '11' => 'A', '12' => 'A', '13' => 'A', '14' => 'A',
        '15' => 'A', '16' => 'A', '17' => 'A', '18' => 'A', '19' => 'A', '20' => 'A',
        '21' => 'A', '22' => 'A', '23' => 'A', '24' => 'A', '25' => 'F', '26' => 'G',
        '27' => 'A', '28' => 'G', '29' => 'G', '30' => 'A', '31' => 'X', '33' => 'F',
        '35' => 'A', '38' => 'A',
    ];

    private array $weights = [
        'A' => [0, 0, 6, 3, 7, 9, 0, 10, 5, 8, 4, 2, 1, 0, 0, 0],
        'B' => [0, 0, 0, 0, 0, 0, 0, 10, 5, 8, 4, 2, 1, 0, 0, 0],
        'D' => [0, 0, 0, 0, 0, 0, 7, 6, 5, 4, 3, 2, 1, 0, 0, 0],
        'E' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 5, 4, 3, 2, 0, 0, 1],
        'F' => [0, 0, 0, 0, 0, 0, 1, 7, 3, 1, 7, 3, 1, 0, 0, 0],
        'G' => [0, 0, 0, 0, 0, 0, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1],
        'X' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
    ];

    private array $moduli = [
        'A' => [11, 11],
        'B' => [11, 11],
        'D' => [11, 11],
        'E' => [9, 11],
        'F' => [10, 10],
        'G' => [9, 10],
        'X' => [1, 1],
    ];

    private function calcChecksum(string $number): int
    {
        $prefix = substr($number, 0, 2);
        $algorithm = $this->algorithms[$prefix] ?? 'X';
        
        $accountBase = substr($number, 6, 7);
        if ($algorithm === 'A' && $accountBase >= '0990000') {
            $algorithm = 'B';
        }

        $weights = $this->weights[$algorithm];
        list($mod1, $mod2) = $this->moduli[$algorithm];

        $sum = 0;
        for ($i = 0; $i < 16; $i++) {
            $c = $weights[$i] * (int)$number[$i];
            if ($c > $mod1) {
                $c = $c % $mod1;
            }
            $sum += $c;
        }

        return $sum % $mod2;
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for BankAccount');
        }

        if (strlen($cleaned) !== 16) {
            return ValidationResult::failure('Invalid length for BankAccount');
        }

        if ($this->calcChecksum($cleaned) !== 0) {
            return ValidationResult::failure('Invalid checksum for BankAccount');
        }

        // We omit the banks.dat validation to avoid relying on a 70KB dictionary for memory concerns.

        return ValidationResult::success();
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $cleaned = $this->compact($number);
        if (strlen($cleaned) === 16) {
            return substr($cleaned, 0, 2) . '-' . substr($cleaned, 2, 4) . '-' . substr($cleaned, 6, 7) . '-' . substr($cleaned, 13, 3);
        }
        return $cleaned;
    }

    public function compact(string $number): string
    {
        $cleaned = trim(str_replace([' ', '.'], '-', $number));
        $parts = explode('-', $cleaned);
        $parts = array_filter($parts, fn($val) => $val !== '');
        $parts = array_values($parts);

        if (count($parts) === 4) {
            return str_pad($parts[0], 2, '0', STR_PAD_LEFT) .
                   str_pad($parts[1], 4, '0', STR_PAD_LEFT) .
                   str_pad($parts[2], 7, '0', STR_PAD_LEFT) .
                   str_pad($parts[3], 3, '0', STR_PAD_LEFT);
        } else {
            $joined = implode('', $parts);
            if (strlen($joined) <= 16 && strlen($joined) >= 13) {
                // otherwise zero pad the account type
                return substr($joined, 0, 13) . str_pad(substr($joined, 13), 3, '0', STR_PAD_LEFT);
            }
            return $joined;
        }
    }
}
