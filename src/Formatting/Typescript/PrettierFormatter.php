<?php

declare(strict_types=1);

namespace TypeSmith\Formatting\Typescript;

use RuntimeException;
use Symfony\Component\Process\Process;
use TypeSmith\Formatting\Formatter;
use TypeSmith\TypeGeneration\GeneratedType;

class PrettierFormatter implements Formatter
{
    public function __construct(
        private readonly ?string $binary = null
    ) {}

    public function format(GeneratedType $generatedType): GeneratedType
    {
        $binary = $this->binary ?? $this->findBinary();

        $process = new Process([$binary, '--parser', 'typescript']);

        $process
            ->setInput($generatedType->content)
            ->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                "Prettier failed to format '%s':\n%s",
                $generatedType->name, $process->getErrorOutput()
            ));
        }

        return new GeneratedType(
            $generatedType->name,
            $generatedType->target,
            $process->getOutput()
        );
    }

    private function findBinary(): string
    {
        $basePath = base_path();

        $candidates = [
            $basePath.'/node_modules/.bin/prettier',
            $basePath.'/node_modules/.bin/prettier.cjs',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException(
            'Prettier could not be found. '.
            'Install Prettier in your project '.
            'or configure the Prettier binary explicitly'
        );
    }
}
