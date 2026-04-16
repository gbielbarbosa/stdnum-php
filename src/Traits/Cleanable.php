<?php

namespace StdNum\Traits;

trait Cleanable
{
    /**
     * Removes all non-numeric characters.
     */
    protected function cleanDigits(string $number): string
    {
        return preg_replace('/[^0-9]/', '', $number) ?? '';
    }
}
