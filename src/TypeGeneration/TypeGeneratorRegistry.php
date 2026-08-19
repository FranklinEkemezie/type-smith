<?php

declare(strict_types=1);

namespace TypeSmith\TypeGeneration;

class TypeGeneratorRegistry
{
    /** @var array<string, TypeGenerator> */
    private array $generators = [];

    public function register(string $language, TypeGenerator $generator): self
    {
        $this->generators[$language] = $generator;

        return $this;
    }

    public function get(string $language): ?TypeGenerator
    {
        return $this->generators[$language] ?? null;
    }
}
