<?php

declare(strict_types=1);

namespace TypeSmith\Formatting;

use TypeSmith\TypeGeneration\GeneratedType;

interface Formatter
{
    public function format(GeneratedType $generatedType): GeneratedType;
}
