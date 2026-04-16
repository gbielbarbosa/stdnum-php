<?php

namespace StdNum\Countries\DE;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;

class STNR implements DocumentInterface
{
    // Mapped straight from python-stdnum
    protected array $formats = [
        ['FFBBBUUUUP', '28FF0BBBUUUUP'],
        ['FFFBBBUUUUP', '9FFF0BBBUUUUP'],
        ['FFBBBUUUUP', '11FF0BBBUUUUP'],
        ['0FFBBBUUUUP', '30FF0BBBUUUUP'],
        ['FFBBBUUUUP', '24FF0BBBUUUUP'],
        ['FFBBBUUUUP', '22FF0BBBUUUUP'],
        ['0FFBBBUUUUP', '26FF0BBBUUUUP'],
        ['0FFBBBUUUUP', '40FF0BBBUUUUP'],
        ['FFBBBUUUUP', '23FF0BBBUUUUP'],
        ['FFFBBBBUUUP', '5FFF0BBBBUUUP'],
        ['FFBBBUUUUP', '27FF0BBBUUUUP'],
        ['0FFBBBUUUUP', '10FF0BBBUUUUP'],
        ['2FFBBBUUUUP', '32FF0BBBUUUUP'],
        ['1FFBBBUUUUP', '31FF0BBBUUUUP'],
        ['FFBBBUUUUP', '21FF0BBBUUUUP'],
        ['1FFBBBUUUUP', '41FF0BBBUUUUP'],
    ];

    public function validate(string $number): ValidationResult
    {
        $compact = $this->compact($number);
        $len = strlen($compact);

        if ($len !== 10 && $len !== 11 && $len !== 13) {
            return ValidationResult::failure('Invalid length for STNR.');
        }

        if (!ctype_digit($compact)) {
            return ValidationResult::failure('STNR must contain only digits.');
        }

        foreach ($this->formats as $formatsPair) {
            foreach ($formatsPair as $format) {
                if ($this->matchesFormat($format, $compact)) {
                    return ValidationResult::success();
                }
            }
        }

        return ValidationResult::failure('Format does not match any valid region structure.');
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
        return trim(strtoupper(str_replace([' ', '-', '.', '/', ','], '', $number)));
    }

    protected function matchesFormat(string $format, string $number): bool
    {
        // Replaces sequences of F B U P into \d{count}
        $pattern = preg_replace_callback('/([FBUP])\1*/', function ($matches) {
            return '\d{' . strlen($matches[0]) . '}';
        }, $format);

        // Prepend start and end delimiters
        $pattern = '/^' . $pattern . '$/';

        return preg_match($pattern, $number) === 1;
    }
}

