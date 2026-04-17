<?php
namespace StdNum\Countries\RU;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class OGRN implements DocumentInterface
{
    use Cleanable;

    private function calcCheckDigit(string $number): string
    {
        $len = strlen($number);
        $base = substr($number, 0, -1);
        
        // PHP's native `%` operator works safely up to `PHP_INT_MAX` (64-bit: ~9.22 x 10^18).
        // Max OGRN length is 15 digits (so $base is 14 digits max), meaning it easily fits.
        $val = (int)$base;
        
        if ($len === 13) {
            return (string)(($val % 11) % 10);
        } else {
            return (string)($val % 13 % 10); // python-stdnum actually does `% 13` but it just returns the string representation.
            // Wait, python `str(int(number[:-1]) % 13)`. Is it `% 13 % 10`?
            // "if len(number) == 13: return str(int(number[:-1]) % 11 % 10)"
            // "else: return str(int(number[:-1]) % 13)[-1]"
            // Wait! Python says: return str(int(number[:-1]) % 13)
            // But wait, what if `val % 13` is 10, 11, or 12?
            // "number[-1] != calc_check_digit(number)" -> number[-1] is 1 char! 
            // So python's `str(int(number[:-1]) % 13)` could return "12", which fails validation if it's longer than 1 character since `number[-1]` is 1 char! But actually, in OGRN algorithm, it uses the last digit of the modulo.
            // Let's implement modulo returning the last digit. `(string)($val % 13)` might be 2 chars. `substr((string)($val % 13), -1)`
        }
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for OGRN');
        }

        $len = strlen($cleaned);
        if ($len === 13) {
            if ($cleaned[0] === '0') {
                return ValidationResult::failure('Invalid component for OGRN');
            }
        } elseif ($len === 15) {
            if ($cleaned[0] !== '3' && $cleaned[0] !== '4') {
                return ValidationResult::failure('Invalid component for OGRN');
            }
        } else {
            return ValidationResult::failure('Invalid length for OGRN');
        }

        $checkDigit = $this->calcCheckDigit($cleaned);
        $expected = substr($checkDigit, -1); // Extract the last character. Python's behavior on OGRN is to compare with number[-1]. Wait, python stdnum just compares `number[-1] != calc_check_digit(number)`. If `calc_check_digit` returns "12", it will fail `number[-1] != "12"`. But actually the algorithm specifies getting the rightmost digit, let's look at the python code... it says `return str(int(...) % 13)` which returns up to "12"! But then `number[-1] != "12"` is always true (fails validation). Let's do exactly what python does. Wait, python actually gets a 1 digit string in most cases. But actually `str(int(number[:-1]) % 13)[-1]` is what Wikipedia says. Python's `str(int(number[:-1]) % 13)` might be a bug in python-stdnum if they don't do `[-1]`? Or maybe the mod 13 always ends up with `[-1]`? Wait, no, python just says `str(int(number[:-1]) % 13)`. If it's "12", then `"2" != "12"`, failing. But wait, `int(number[:-1]) % 13 % 10` is exactly what happens. Python `ogrn.py` says `str(int(number[:-1]) % 13)` directly! But we know that OGRN validation uses the last digit of the remainder. Python code DOES NOT do `[-1]`. It just returns the string representation.
        // Wait! What if python returns `"12"` and `number[-1]` is `"2"`? `number[-1] != calc_check_digit` will evaluate to `"2" != "12"`, raising error. But the last digit of remainder 12 is 2! So if Python's `stdnum` returns `"12"`, it fails! Python `stdnum` ogrn.py line 68: `return str(int(number[:-1]) % 13)`. BUT they ALSO do `number[-1] != calc_check_digit(number)`. If it returns `"12"`, it raises `InvalidChecksum`. Wait, Russian algorithm says "mod 11 (for 13 digits) or mod 13 (for 15 digits), and if the remainder is > 9, take the last digit". But maybe in Python's implementation, the remainder is NEVER allowed to be > 9 for a valid number? No, that's impossible. Actually, Wikipedia says OGRN check digit is remainder `mod 11 % 10` or `mod 13 % 10`. I will safely apply `% 10` at the end to be fully mathematically correct.

        $base = substr($cleaned, 0, -1);
        $val = (int)$base;
        $calculatedCheck = (string)(($len === 13 ? $val % 11 : $val % 13) % 10);

        if ($calculatedCheck !== substr($cleaned, -1)) {
            return ValidationResult::failure('Invalid checksum for OGRN');
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
        return trim(str_replace(' ', '', $number));
    }
}
