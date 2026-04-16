<?php

namespace StdNum\Exceptions;

class UnsupportedDocumentTypeException extends StdNumException
{
    public function __construct(string $type)
    {
        parent::__construct(sprintf('The document type "%s" is not supported or not found.', $type));
    }
}
