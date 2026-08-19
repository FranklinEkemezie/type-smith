<?php

declare(strict_types=1);

namespace TypeSmith\TypeGeneration\Typescript;

use TypeSmith\Exceptions\UnsupportedTypeDefinitionException;
use TypeSmith\TypeDefinitions\TypeDefinition;
use TypeSmith\TypeGeneration\GeneratedType;
use TypeSmith\TypeGeneration\TypeGenerator;

class TypescriptTypeGenerator implements TypeGenerator
{
    /**
     * @param  iterable<TypeDefinitionTypeGenerator>  $generators
     */
    public function __construct(
        protected iterable $generators,
    ) {}

    /**
     * @throws UnsupportedTypeDefinitionException
     */
    public function generate(TypeDefinition $typeDefinition): GeneratedType
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($typeDefinition)) {
                return $generator->generate($typeDefinition);
            }
        }

        throw new UnsupportedTypeDefinitionException($typeDefinition::class);
    }
}
