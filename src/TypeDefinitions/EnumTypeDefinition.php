<?php

declare(strict_types=1);

namespace TypeSmith\TypeDefinitions;

class EnumTypeDefinition implements TypeDefinition
{
    /**
     * @param  EnumCase[]  $cases
     */
    public function __construct(
        public string $name,
        public ?string $backingType,
        public array $cases
    ) {}

}
