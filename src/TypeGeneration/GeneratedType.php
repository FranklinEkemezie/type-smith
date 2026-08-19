<?php

declare(strict_types=1);

namespace TypeSmith\TypeGeneration;

use TypeSmith\GenerationPipeline\GenerationTarget;

class GeneratedType
{
    public function __construct(
        public string $name,
        public GenerationTarget $target,
        public string $content
    ) {}
}
