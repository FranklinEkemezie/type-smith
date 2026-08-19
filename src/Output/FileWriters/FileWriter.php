<?php

declare(strict_types=1);

namespace TypeSmith\Output\FileWriters;

use TypeSmith\Output\GeneratedFile;
use TypeSmith\TypeGeneration\GeneratedType;

interface FileWriter
{
    public function write(GeneratedType $generatedType, string $directory): GeneratedFile;
}
