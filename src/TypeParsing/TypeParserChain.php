<?php

declare(strict_types=1);

namespace TypeSmith\TypeParsing;

use TypeSmith\Exceptions\UnsupportedClassTypeException;
use TypeSmith\TypeDefinitions\TypeDefinition;

class TypeParserChain
{
    /**
     * @param  list<TypeParser>  $parsers
     */
    public function __construct(protected array $parsers) {}

    /**
     * @param  class-string<*>  $className
     *
     * @throws UnsupportedClassTypeException
     */
    public function parse(string $className): TypeDefinition
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($className)) {
                return $parser->parse($className);
            }
        }

        throw new UnsupportedClassTypeException($className);
    }
}
