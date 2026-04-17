<?php
namespace StdNum\Countries\IN;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class PAN implements DocumentInterface
{
    use Cleanable;

    private $holderTypes = [
        'A' => 'Association of Persons (AOP)',
        'B' => 'Body of Individuals (BOI)',
        'C' => 'Company',
        'F' => 'Firm/Limited Liability Partnership',
        'G' => 'Government Agency',
        'H' => 'Hindu Undivided Family (HUF)',
        'L' => 'Local Authority',
        'J' => 'Artificial Juridical Person',
        'P' => 'Individual',
        'T' => 'Trust',
        'K' => 'Krish (Trust Krish)',
    ];

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (strlen($cleaned) !== 10) {
            return ValidationResult::failure('Invalid length for PAN');
        }

        if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $cleaned)) {
            return ValidationResult::failure('Invalid format for PAN');
        }

        if (!isset($this->holderTypes[$cleaned[3]])) {
            return ValidationResult::failure('Invalid component for PAN');
        }

        if (substr($cleaned, 5, 4) === '0000') {
            return ValidationResult::failure('Invalid component for PAN');
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

    public function info(string $number): ?array
    {
        $cleaned = $this->compact($number);
        if (!isset($this->holderTypes[$cleaned[3] ?? ''])) {
            return null;
        }

        return [
            'holder_type' => $this->holderTypes[$cleaned[3]],
            'card_holder_type' => $this->holderTypes[$cleaned[3]],
            'initial' => $cleaned[4],
        ];
    }

    public function mask(string $number): string
    {
        $compact = $this->compact($number);
        if (strlen($compact) >= 10) {
            return substr($compact, 0, 5) . 'XXXX' . substr($compact, -1);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }
}
