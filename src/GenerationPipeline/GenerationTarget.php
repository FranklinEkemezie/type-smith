<?php

declare(strict_types=1);

namespace TypeSmith\GenerationPipeline;

class GenerationTarget
{
    public function __construct(
        public string $language,
        public string $extension
    ) {}
}
