<?php

declare(strict_types=1);

namespace TypeSmith\SourceResolution;

class SourceResolverChain
{
    /** @var SourceResolver[] */
    protected array $resolvers = [];

    public function __construct(SourceResolver ...$resolvers)
    {
        $this->resolvers = $resolvers;
    }

    /**
     * @return list<class-string>
     */
    public function resolve(string $source): array
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($source)) {
                return $resolver->resolve($source);
            }
        }

        return [];
    }
}
