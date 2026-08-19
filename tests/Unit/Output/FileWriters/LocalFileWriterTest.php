<?php

declare(strict_types=1);

namespace TypeSmith\Tests\Unit\Output\FileWriters;

use Illuminate\Filesystem\Filesystem;
use Mockery;
use TypeSmith\GenerationPipeline\GenerationTarget;
use TypeSmith\Output\FileWriters\LocalFileWriter;
use TypeSmith\Output\GeneratedFile;
use TypeSmith\TypeGeneration\GeneratedType;

beforeEach(function () {
    $this->filesystem = Mockery::mock(Filesystem::class);
    $this->writer = new LocalFileWriter($this->filesystem);
});

it('writes the generated type to the specified directory',
    function (GeneratedType $generatedType, string $directory, string $content, string $filePath) {

        $this->filesystem
            ->shouldReceive('ensureDirectoryExists')
            ->with($directory);

        $this->filesystem
            ->shouldReceive('put')
            ->once()
            ->with($filePath, $content);

        $file = $this->writer->write($generatedType, $directory);

        expect($file)->toBeInstanceOf(GeneratedFile::class)
            ->and($file->path)->toBe($filePath);
    })->with([
        //    [
        //        'generatedType' => new GeneratedType(
        //            'Status',
        //            new GenerationTarget('typescript', 'ts'),
        //            "export type Status = 'draft' | 'published';"
        //        ),
        //        'directory'     => 'resources/js/types',
        //        'content'       => "export type Status = 'draft' | 'published';",
        //        'filePath'      => 'resources/js/types/Status.ts'
        //    ],
        [
            new GeneratedType(
                'Status',
                new GenerationTarget('typescript', 'ts'),
                "export type Status = 'draft' | 'published';"
            ),
            'resources/js/types',
            "export type Status = 'draft' | 'published';",
            'resources/js/types/Status.ts',
        ],
        [
            new GeneratedType(
                'User',
                new GenerationTarget('kotlin', 'kt'),
                'data class User(...)'
            ),
            'generated',
            'data class User(...)',
            'generated/User.kt',
        ],
    ]);
