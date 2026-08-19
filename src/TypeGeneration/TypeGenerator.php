<?php

declare(strict_types=1);

namespace TypeSmith\TypeGeneration;

use TypeSmith\TypeDefinitions\TypeDefinition;

interface TypeGenerator
{
    public function generate(TypeDefinition $typeDefinition): GeneratedType;
}
