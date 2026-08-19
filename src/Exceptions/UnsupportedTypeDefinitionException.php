<?php

declare(strict_types=1);

namespace TypeSmith\Exceptions;

class UnsupportedTypeDefinitionException extends Exception
{
    public function __construct(protected string $typeDefinitionClass)
    {
        parent::__construct("Unsupported type definition [$typeDefinitionClass]");
    }
}
