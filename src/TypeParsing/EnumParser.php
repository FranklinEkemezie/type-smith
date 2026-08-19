<?php

declare(strict_types=1);

namespace TypeSmith\TypeParsing;

use BackedEnum;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use ReflectionEnumUnitCase;
use ReflectionException;
use TypeSmith\TypeDefinitions\EnumCase;
use TypeSmith\TypeDefinitions\EnumTypeDefinition;
use UnitEnum;

final class EnumParser implements TypeParser
{
    /**
     * @param  class-string  $className
     */
    public function supports(string $className): bool
    {
        return enum_exists($className);
    }

    /**
     * @param  class-string<BackedEnum|UnitEnum>  $className
     *
     * @throws ReflectionException
     */
    public function parse(string $className): EnumTypeDefinition
    {
        $enumReflection = new ReflectionEnum($className);

        $name = $enumReflection->getName();
        $backingType = $enumReflection->getBackingType()?->getName();
        $cases = collect($enumReflection->getCases())->map(
            fn (ReflectionEnumUnitCase|ReflectionEnumBackedCase $case) => new EnumCase($case->getName(),
                $case instanceof ReflectionEnumBackedCase ?
                    $case->getBackingValue() : $case->getName())
        )
            ->all();

        return new EnumTypeDefinition($name, $backingType, $cases);
    }
}
