<?php

declare(strict_types=1);

namespace TypeSmith\TypeGeneration\Typescript;

use InvalidArgumentException;
use TypeSmith\GenerationPipeline\GenerationTarget;
use TypeSmith\TypeDefinitions\EnumCase;
use TypeSmith\TypeDefinitions\EnumTypeDefinition;
use TypeSmith\TypeDefinitions\TypeDefinition;
use TypeSmith\TypeGeneration\GeneratedType;

class EnumDefinitionTypeGenerator implements TypeDefinitionTypeGenerator
{
    public function supports(TypeDefinition $definition): bool
    {
        return $definition instanceof EnumTypeDefinition;
    }

    public function generate(TypeDefinition $definition): GeneratedType
    {
        if (! $definition instanceof EnumTypeDefinition) {
            throw new InvalidArgumentException(sprintf(
                "Expected type '%s', got type '%s'",
                EnumTypeDefinition::class, $definition::class
            ));
        }

        $typeName = class_basename($definition->name);
        $values = collect($definition->cases)
            ->map(fn (EnumCase $case) => ($definition->backingType !== null ?
                "'$case->value'" : "'$case->name'"))
            ->implode(' | ');
        $content = "export type $typeName = $values";
        $target = new GenerationTarget('typescript', 'ts');

        return new GeneratedType($typeName, $target, $content);
    }
}
