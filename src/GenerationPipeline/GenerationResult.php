<?php

declare(strict_types=1);

namespace TypeSmith\GenerationPipeline;

use Throwable;
use TypeSmith\Output\GeneratedFile;
use TypeSmith\TypeGeneration\GeneratedType;

class GenerationResult
{
    /** @var list<GeneratedFile> */
    protected array $files = [];

    /** @var list<GeneratedType> */
    protected array $types = [];

    /** @var list<GeneratedType> */
    protected array $formattedTypes = [];

    /** @var list<GenerationError> */
    protected array $errors = [];

    public function addGeneratedFile(GeneratedFile $file): self
    {
        $this->files[] = $file;

        return $this;
    }

    public function addGeneratedType(GeneratedType $type): self
    {
        $this->types[] = $type;

        return $this;
    }

    public function addFormattedType(GeneratedType $type): self
    {
        $this->formattedTypes[] = $type;

        return $this;
    }

    public function addError(GenerationStage $stage, Throwable $exception, ?string $className = null): self
    {
        $this->errors[] = new GenerationError($stage, $exception, $className);

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->files) && empty($this->errors);
    }

    public function successfulCount(): int
    {
        return $this->writtenCount();
    }

    public function generatedCount(): int
    {
        return count($this->types);
    }

    public function formattedCount(): int
    {
        return count($this->formattedTypes);
    }

    public function writtenCount(): int
    {
        return count($this->files);
    }

    public function failedCount(): int
    {
        return count($this->errors);
    }

    public function isSuccessful(): bool
    {
        return $this->failedCount() === 0;
    }

    /**
     * @return GeneratedFile[]
     */
    public function getGeneratedFiles(): array
    {
        return $this->files;
    }

    /**
     * @return GeneratedType[]
     */
    public function getGeneratedTypes(): array
    {
        return $this->types;
    }

    /**
     * @return GeneratedType[]
     */
    public function getFormattedTypes(): array
    {
        return $this->formattedTypes;
    }

    /**
     * @return GenerationError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
