<?php

declare(strict_types=1);

namespace TypeSmith\SourceResolution;

interface SourceResolver
{
    public function supports(string $source): bool;

    /**
     * @return list<class-string>
     */
    public function resolve(string $source): array;
}
