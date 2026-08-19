<?php

declare(strict_types=1);

namespace TypeSmith\SourceResolution;

class ExplicitClassSourceResolver implements SourceResolver
{
    public function supports(string $source): bool
    {
        return class_exists($source);
    }

    /**
     * @param  class-string  $source
     * @return list<class-string>
     */
    public function resolve(string $source): array
    {
        return [$source];
    }
}
