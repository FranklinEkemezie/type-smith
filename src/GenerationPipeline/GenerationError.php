<?php

declare(strict_types=1);

namespace TypeSmith\GenerationPipeline;

use Throwable;

class GenerationError
{
    public function __construct(
        public GenerationStage $stage,
        public Throwable $exception,
        public ?string $className,
    ) {}
}
