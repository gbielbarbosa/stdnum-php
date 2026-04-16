<?php

namespace StdNum\Laravel\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use StdNum\Exceptions\UnsupportedDocumentTypeException;
use StdNum\StdNum;

class StdNumRule implements ValidationRule
{
    protected string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) && !is_numeric($value)) {
            $fail('The :attribute must be a string or number.')->translate();
            return;
        }

        try {
            $validator = StdNum::make($this->type);
            $result = $validator->validate((string) $value);

            if (!$result->isValid) {
                // Return default english error.
                $fail($result->error ?? 'The :attribute is not a valid ' . strtoupper(str_replace('.', ' ', $this->type)) . ' number.')->translate();
            }
        } catch (UnsupportedDocumentTypeException $e) {
            $fail('The document type provided for :attribute validation is not supported.')->translate();
        }
    }
}
