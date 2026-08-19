<?php

declare(strict_types=1);

namespace TypeSmith\TypeGeneration\Typescript;

use TypeSmith\TypeDefinitions\TypeDefinition;
use TypeSmith\TypeGeneration\GeneratedType;

interface TypeDefinitionTypeGenerator
{
    public function supports(TypeDefinition $definition): bool;

    public function generate(TypeDefinition $definition): GeneratedType;
}
