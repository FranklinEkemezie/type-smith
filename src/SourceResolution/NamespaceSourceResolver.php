<?php

declare(strict_types=1);

namespace TypeSmith\SourceResolution;

use Composer\Autoload\ClassLoader;
use FilesystemIterator;
use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

readonly class NamespaceSourceResolver implements SourceResolver
{
    public function __construct(
        private ClassLoader $loader
    ) {}

    public function supports(string $source): bool
    {
        $mapping = $this->resolveBestPsr4Mapping($source);

        if (is_null($mapping)) {
            return false;
        }

        $directory = $this->resolveNamespaceDirectory($source, $mapping);

        return ! is_null($directory);
    }

    public function resolve(string $source): array
    {
        $sourceMapping = $this->resolveBestPsr4Mapping($source);
        if ($sourceMapping === null) {
            throw new RuntimeException("Could not resolve a namespace for $source");
        }

        $namespaceDirectory = $this->resolveNamespaceDirectory($source, $sourceMapping);
        if ($namespaceDirectory === null) {
            throw new RuntimeException("Namespace [$source] not found.");
        }

        return $this->discoverFromDirectory($source, $namespaceDirectory);
    }

    /**
     * @return array{prefix: string, directory: string}|null
     */
    private function resolveBestPsr4Mapping(string $source): ?array
    {
        /** @var ?string $bestPrefix */
        $bestPrefix = null;
        /** @var ?string $bestDirectory */
        $bestDirectory = null;

        foreach ($this->loader->getPrefixesPsr4() as $prefix => $directories) {
            if ($source !== $prefix && ! str_starts_with($source, $prefix)) {
                continue;
            }

            foreach ($directories as $directory) {
                if (! is_dir($directory)) {
                    continue;
                }

                if (strlen($prefix) > strlen($bestPrefix ?? '')) {
                    $bestPrefix = $prefix;
                    $bestDirectory = $directory;
                }
            }
        }

        if (is_null($bestPrefix) || is_null($bestDirectory)) {
            return null;
        }

        return [
            'prefix' => $bestPrefix,
            'directory' => $bestDirectory,
        ];
    }

    /**
     * @param  array{prefix: string, directory: string}  $mapping
     */
    private function resolveNamespaceDirectory(string $source, array $mapping): ?string
    {
        $prefix = $mapping['prefix'];
        $directory = $mapping['directory'];

        $relativeNamespace = substr($source, strlen($prefix));
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeNamespace);
        $relativePath = ltrim($relativePath, DIRECTORY_SEPARATOR);

        $targetDirectory = $directory;
        if ($relativePath !== '') {
            $targetDirectory .= DIRECTORY_SEPARATOR.$relativePath;
        }

        return is_dir($targetDirectory) ? $targetDirectory : null;
    }

    /**
     * @return list<class-string>
     */
    private function discoverFromDirectory(string $source, string $directory): array
    {
        $classes = [];

        foreach ($this->findPhpFiles($directory) as $file) {
            /** @var SplFileInfo $file */
            $classes[] = $this->classNameFromPsr4Path($file, $source, $directory);
        }

        return $classes;
    }

    /**
     * @return Generator<int, SplFileInfo>
     */
    private function findPhpFiles(string $directory): Generator
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $directory,
                FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
                yield $file;
            }
        }
    }

    /**
     * @return class-string
     */
    private function classNameFromPsr4Path(
        SplFileInfo $fileInfo,
        string $source,
        string $directory
    ): string {
        $relativePath = substr(
            $fileInfo->getPathname(),
            strlen($directory) + 1
        );

        /** @var class-string $className */
        $className = pathinfo($relativePath, PATHINFO_FILENAME);

        $relativeDirectory = dirname($relativePath);

        if ($relativeDirectory !== '.') {
            $relativeDirectory = str_replace(
                DIRECTORY_SEPARATOR,
                '\\',
                $relativeDirectory
            );

            $className = $relativeDirectory.'\\'.$className;
        }

        /** @var class-string $resolvedClassName */
        $resolvedClassName = $source.'\\'.$className;

        return $resolvedClassName;
    }
}
