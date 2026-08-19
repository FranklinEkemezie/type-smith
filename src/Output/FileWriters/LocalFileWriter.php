<?php

declare(strict_types=1);

namespace TypeSmith\Output\FileWriters;

use Illuminate\Filesystem\Filesystem;
use TypeSmith\Output\GeneratedFile;
use TypeSmith\TypeGeneration\GeneratedType;

readonly class LocalFileWriter implements FileWriter
{
    public function __construct(
        private Filesystem $filesystem
    ) {}

    public function write(GeneratedType $generatedType, string $directory): GeneratedFile
    {
        $this->filesystem->ensureDirectoryExists($directory);

        $path =
            $directory.'/'.
            $generatedType->name.'.'.
            $generatedType->target->extension;

        $this->filesystem->put($path, $generatedType->content);

        return new GeneratedFile($path);

    }
}
