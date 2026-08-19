<?php

declare(strict_types=1);

namespace TypeSmith\TypeParsing;

use TypeSmith\TypeDefinitions\TypeDefinition;

interface TypeParser
{
    /**
     * @param  class-string  $className
     */
    public function supports(string $className): bool;

    /**
     * @param  class-string  $className
     */
    public function parse(string $className): TypeDefinition;
}
