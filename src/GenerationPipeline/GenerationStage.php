<?php

declare(strict_types=1);

namespace TypeSmith\GenerationPipeline;

use LogicException;

enum GenerationStage: int
{
    case Resolving = 1;
    case Parsing = 2;
    case Generation = 3;
    case Formatting = 4;
    case Writing = 5;

    public static function first(): self
    {
        return self::Resolving;
    }

    public static function last(): self
    {
        return self::Writing;
    }

    public function next(): GenerationStage
    {
        return self::tryFrom($this->value + 1)
            ?? throw new LogicException("Generation stage '$this->name' has no next stage.");
    }

    public function previous(): GenerationStage
    {
        return self::tryFrom($this->value - 1)
            ?? throw new LogicException("Generation stage '$this->name' has no previous stage.");
    }

    public function label(): string
    {
        return match ($this) {
            self::Resolving => 'Resolving',
            self::Parsing => 'Parsing',
            self::Generation => 'Generation',
            self::Formatting => 'Formatting',
            self::Writing => 'Writing',
        };
    }
}
