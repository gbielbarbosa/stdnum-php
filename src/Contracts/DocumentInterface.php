<?php

namespace StdNum\Contracts;

use StdNum\Models\ValidationResult;

interface DocumentInterface
{
    /**
     * Validates the document number, returning a status object.
     */
    public function validate(string $number): ValidationResult;

    /**
     * Checks whether the document number is valid.
     */
    public function isValid(string $number): bool;

    /**
     * Returns the formatted document number with punctuation.
     */
    public function format(string $number): string;

    /**
     * Returns the document number containing only the significant digits (base numbers/letters).
     */
    public function compact(string $number): string;
}
