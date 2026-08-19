<?php

declare(strict_types=1);

namespace TypeSmith\GenerationPipeline;

readonly class GenerationRequest
{
    public function __construct(
        public string $source,
        public string $language,
        public string $output,
        public bool $shouldFormat
    ) {}

}
