<?php

declare(strict_types=1);

namespace TypeSmith\TypeDefinitions;

final readonly class EnumCase
{
    public function __construct(
        public string $name,
        public string|int|null $value,
    ) {}
}
