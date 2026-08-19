<?php

declare(strict_types=1);

namespace TypeSmith\Tests\Unit\GenerationPipeline;

use Mockery;
use RuntimeException;
use TypeSmith\Exceptions\UnsupportedLanguageException;
use TypeSmith\Formatting\Formatter;
use TypeSmith\GenerationPipeline\GenerateTypes;
use TypeSmith\GenerationPipeline\GenerationRequest;
use TypeSmith\GenerationPipeline\GenerationResult;
use TypeSmith\GenerationPipeline\GenerationStage;
use TypeSmith\Output\FileWriters\FileWriter;
use TypeSmith\Output\GeneratedFile;
use TypeSmith\SourceResolution\SourceResolverChain;
use TypeSmith\TypeDefinitions\TypeDefinition;
use TypeSmith\TypeGeneration\GeneratedType;
use TypeSmith\TypeGeneration\TypeGenerator;
use TypeSmith\TypeGeneration\TypeGeneratorRegistry;
use TypeSmith\TypeParsing\TypeParserChain;

beforeEach(function () {
    $this->resolver = Mockery::mock(SourceResolverChain::class);
    $this->parser = Mockery::mock(TypeParserChain::class);
    $this->registry = Mockery::mock(TypeGeneratorRegistry::class);
    $this->generator = Mockery::mock(TypeGenerator::class);
    $this->formatter = Mockery::mock(Formatter::class);
    $this->writer = Mockery::mock(FileWriter::class);

    $this->service = new GenerateTypes(
        $this->resolver,
        $this->parser,
        $this->registry,
        $this->formatter,
        $this->writer,
    );
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function request(
    string $source = 'App\\Enums',
    string $language = 'typescript',
    string $output = 'resources/js/types',
    bool $format = false,
): GenerationRequest {
    return new GenerationRequest($source, $language, $output, $format);
}

/*
|--------------------------------------------------------------------------
| Successful generation
|--------------------------------------------------------------------------
*/

it('generates and writes all resolved types', function () {
    $request = request();

    $statusDefinition = Mockery::mock(TypeDefinition::class);
    $roleDefinition = Mockery::mock(TypeDefinition::class);

    $statusType = Mockery::mock(GeneratedType::class);
    $roleType = Mockery::mock(GeneratedType::class);

    $statusFile = Mockery::mock(GeneratedFile::class);
    $roleFile = Mockery::mock(GeneratedFile::class);

    $this->registry
        ->shouldReceive('get')
        ->once()
        ->with('typescript')
        ->andReturn($this->generator);

    $this->resolver
        ->shouldReceive('resolve')
        ->once()
        ->with('App\\Enums')
        ->andReturn([
            'App\\Enums\\Status',
            'App\\Enums\\Role',
        ]);

    $this->parser
        ->shouldReceive('parse')
        ->once()
        ->with('App\\Enums\\Status')
        ->andReturn($statusDefinition);

    $this->parser
        ->shouldReceive('parse')
        ->once()
        ->with('App\\Enums\\Role')
        ->andReturn($roleDefinition);

    $this->generator
        ->shouldReceive('generate')
        ->once()
        ->with($statusDefinition)
        ->andReturn($statusType);

    $this->generator
        ->shouldReceive('generate')
        ->once()
        ->with($roleDefinition)
        ->andReturn($roleType);

    $this->formatter->shouldNotReceive('format');

    $this->writer
        ->shouldReceive('write')
        ->once()
        ->with($statusType, 'resources/js/types')
        ->andReturn($statusFile);

    $this->writer
        ->shouldReceive('write')
        ->once()
        ->with($roleType, 'resources/js/types')
        ->andReturn($roleFile);

    $result = $this->service->execute($request);

    expect($result)->toBeInstanceOf(GenerationResult::class)
        ->and($result->getGeneratedTypes())->toBe([$statusType, $roleType])
        ->and($result->getGeneratedFiles())->toBe([$statusFile, $roleFile])
        ->and($result->getErrors())->toBeEmpty();

});

/*
|--------------------------------------------------------------------------
| Formatting
|--------------------------------------------------------------------------
*/

it('formats generated types when formatting is enabled', function () {
    $request = request(format: true);

    $definition = Mockery::mock(TypeDefinition::class);
    $generatedType = Mockery::mock(GeneratedType::class);
    $formattedType = Mockery::mock(GeneratedType::class);
    $file = Mockery::mock(GeneratedFile::class);

    $this->registry
        ->shouldReceive('get')
        ->once()
        ->with('typescript')
        ->andReturn($this->generator);

    $this->resolver
        ->shouldReceive('resolve')
        ->once()
        ->with('App\\Enums')
        ->andReturn(['App\\Enums\\Status']);

    $this->parser
        ->shouldReceive('parse')
        ->once()
        ->with('App\\Enums\\Status')
        ->andReturn($definition);

    $this->generator
        ->shouldReceive('generate')
        ->once()
        ->with($definition)
        ->andReturn($generatedType);

    $this->formatter
        ->shouldReceive('format')
        ->once()
        ->with($generatedType)
        ->andReturn($formattedType);

    $this->writer
        ->shouldReceive('write')
        ->once()
        ->with($formattedType, 'resources/js/types')
        ->andReturn($file);

    $result = $this->service->execute($request);

    expect($result->getGeneratedTypes())->toEqualCanonicalizing([$formattedType])
        ->and($result->getGeneratedFiles())->toBe([$file])
        ->and($result->getFormattedTypes())->toBe([$formattedType])
        ->and($result->getErrors())->toBeEmpty();

});

it('does not format generated types when formatting is disabled', function () {
    $request = request();

    $definition = Mockery::mock(TypeDefinition::class);
    $generatedType = Mockery::mock(GeneratedType::class);
    $file = Mockery::mock(GeneratedFile::class);

    $this->registry
        ->shouldReceive('get')
        ->once()
        ->with('typescript')
        ->andReturn($this->generator);

    $this->resolver
        ->shouldReceive('resolve')
        ->once()
        ->andReturn(['App\\Enums\\Status']);

    $this->parser
        ->shouldReceive('parse')
        ->once()
        ->with('App\\Enums\\Status')
        ->andReturn($definition);

    $this->generator
        ->shouldReceive('generate')
        ->once()
        ->with($definition)
        ->andReturn($generatedType);

    $this->formatter
        ->shouldNotReceive('format');

    $this->writer
        ->shouldReceive('write')
        ->once()
        ->with($generatedType, 'resources/js/types')
        ->andReturn($file);

    $result = $this->service->execute($request);

    expect($result->getGeneratedTypes())->toBe([$generatedType])
        ->and($result->getGeneratedFiles())->toBe([$file])
        ->and($result->getFormattedTypes())->toBeEmpty()
        ->and($result->getErrors())->toBeEmpty();

});

/*
|--------------------------------------------------------------------------
| Empty resolution
|--------------------------------------------------------------------------
*/

it('returns an empty successful result when no classes are resolved', function () {
    $request = request();

    $this->registry
        ->shouldReceive('get')
        ->once()
        ->with('typescript')
        ->andReturn($this->generator);

    $this->resolver
        ->shouldReceive('resolve')
        ->once()
        ->with('App\\Enums')
        ->andReturn([]);

    $this->parser->shouldNotReceive('parse');
    $this->generator->shouldNotReceive('generate');
    $this->formatter->shouldNotReceive('format');
    $this->writer->shouldNotReceive('write');

    $result = $this->service->execute($request);

    expect($result->getGeneratedTypes())->toBeEmpty()
        ->and($result->getGeneratedFiles())->toBeEmpty()
        ->and($result->getErrors())->toBeEmpty()
        ->and($result->getErrors())->toBeEmpty();

});

/*
|--------------------------------------------------------------------------
| Parsing failures
|--------------------------------------------------------------------------
*/

it('records a parsing failure and continues with remaining classes', function () {
    $request = request();

    $statusDefinition = Mockery::mock(TypeDefinition::class);
    $statusType = Mockery::mock(GeneratedType::class);
    $statusFile = Mockery::mock(GeneratedFile::class);

    $exception = new RuntimeException('Unsupported enum definition');

    $this->registry
        ->shouldReceive('get')
        ->once()
        ->with('typescript')
        ->andReturn($this->generator);

    $this->resolver
        ->shouldReceive('resolve')
        ->once()
        ->andReturn([
            'App\\Enums\\Status',
            'App\\Enums\\Role',
            'App\\Enums\\OrderStatus',
        ]);

    $this->parser
        ->shouldReceive('parse')
        ->once()
        ->with('App\\Enums\\Status')
        ->andReturn($statusDefinition);

    $this->parser
        ->shouldReceive('parse')
        ->once()
        ->with('App\\Enums\\Role')
        ->andThrow($exception);

    $this->parser
        ->shouldReceive('parse')
        ->once()
        ->with('App\\Enums\\OrderStatus')
        ->andReturn(Mockery::mock(TypeDefinition::class));

    $this->generator
        ->shouldReceive('generate')
        ->once()
        ->with($statusDefinition)
        ->andReturn($statusType);

    $this->generator
        ->shouldReceive('generate')
        ->once()
        ->withArgs(function ($definition) {
            return $definition instanceof TypeDefinition;
        })
        ->andReturn(Mockery::mock(GeneratedType::class));

    $this->writer
        ->shouldReceive('write')
        ->once()
        ->with($statusType, 'resources/js/types')
        ->andReturn($statusFile);

    $this->writer
        ->shouldReceive('write')
        ->once()
        ->withArgs(function ($type) {
            return $type instanceof GeneratedType;
        })
        ->andReturn(Mockery::mock(GeneratedFile::class));

    $result = $this->service->execute($request);

    expect($result->getGeneratedTypes())->toHaveCount(2)
        ->and($result->getGeneratedFiles())->toHaveCount(2)
        ->and($result->getErrors())->toHaveCount(1)
        ->and($result->getErrors()[0]->stage)->toBe(GenerationStage::Parsing)
        ->and($result->getErrors()[0]->className)->toBe('App\\Enums\\Role')
        ->and($result->getErrors()[0]->exception)->toBe($exception);

});

/*
|--------------------------------------------------------------------------
| Generation failures
|--------------------------------------------------------------------------
*/

it('records a generation failure and does not format or write that type', function () {
    $request = request(format: true);

    $definition = Mockery::mock(TypeDefinition::class);
    $exception = new RuntimeException('Generation failed');

    $this->registry
        ->shouldReceive('get')
        ->once()
        ->with('typescript')
        ->andReturn($this->generator);

    $this->resolver
        ->shouldReceive('resolve')
        ->once()
        ->andReturn(['App\\Enums\\Status']);

    $this->parser
        ->shouldReceive('parse')
        ->once()
        ->with('App\\Enums\\Status')
        ->andReturn($definition);

    $this->generator
        ->shouldReceive('generate')
        ->once()
        ->with($definition)
        ->andThrow($exception);

    $this->formatter->shouldNotReceive('format');
    $this->writer->shouldNotReceive('write');

    $result = $this->service->execute($request);

    expect($result->getGeneratedTypes())->toBeEmpty()
        ->and($result->getGeneratedFiles())->toBeEmpty()
        ->and($result->getErrors())->toHaveCount(1)
        ->and($result->getErrors()[0]->stage)->toBe(GenerationStage::Generation)
        ->and($result->getErrors()[0]->className)->toBe('App\\Enums\\Status')
        ->and($result->getErrors()[0]->exception)->toBe($exception);

});

/*
|--------------------------------------------------------------------------
| Formatting failures
|--------------------------------------------------------------------------
*/

it('records a formatting failure and does not write the type', function () {
    $request = request(format: true);

    $definition = Mockery::mock(TypeDefinition::class);
    $generatedType = Mockery::mock(GeneratedType::class);

    $exception = new RuntimeException('Formatting failed');

    $this->registry
        ->shouldReceive('get')
        ->once()
        ->with('typescript')
        ->andReturn($this->generator);

    $this->resolver
        ->shouldReceive('resolve')
        ->once()
        ->andReturn(['App\\Enums\\Status']);

    $this->parser
        ->shouldReceive('parse')
        ->once()
        ->with('App\\Enums\\Status')
        ->andReturn($definition);

    $this->generator
        ->shouldReceive('generate')
        ->once()
        ->with($definition)
        ->andReturn($generatedType);

    $this->formatter
        ->shouldReceive('format')
        ->once()
        ->with($generatedType)
        ->andThrow($exception);

    $this->writer->shouldNotReceive('write');

    $result = $this->service->execute($request);

    expect($result->getGeneratedTypes())->toBe([$generatedType])
        ->and($result->getGeneratedFiles())->toBeEmpty()
        ->and($result->getErrors())->toHaveCount(1)
        ->and($result->getErrors()[0]->stage)->toBe(GenerationStage::Formatting)
        ->and($result->getErrors()[0]->className)->toBe('App\\Enums\\Status')
        ->and($result->getErrors()[0]->exception)->toBe($exception);

});

/*
|--------------------------------------------------------------------------
| Writing failures
|--------------------------------------------------------------------------
*/

it('records a writing failure while retaining the generated type', function () {
    $request = request();

    $definition = Mockery::mock(TypeDefinition::class);
    $generatedType = Mockery::mock(GeneratedType::class);

    $exception = new RuntimeException('Unable to write file');

    $this->registry
        ->shouldReceive('get')
        ->once()
        ->with('typescript')
        ->andReturn($this->generator);

    $this->resolver
        ->shouldReceive('resolve')
        ->once()
        ->andReturn(['App\\Enums\\Status']);

    $this->parser
        ->shouldReceive('parse')
        ->once()
        ->andReturn($definition);

    $this->generator
        ->shouldReceive('generate')
        ->once()
        ->with($definition)
        ->andReturn($generatedType);

    $this->writer
        ->shouldReceive('write')
        ->once()
        ->with($generatedType, 'resources/js/types')
        ->andThrow($exception);

    $result = $this->service->execute($request);

    expect($result->getGeneratedTypes())->toBe([$generatedType])
        ->and($result->getGeneratedFiles())->toBeEmpty()
        ->and($result->getErrors())->toHaveCount(1)
        ->and($result->getErrors()[0]->stage)->toBe(GenerationStage::Writing)
        ->and($result->getErrors()[0]->className)->toBe('App\\Enums\\Status')
        ->and($result->getErrors()[0]->exception)->toBe($exception);

});

/*
|--------------------------------------------------------------------------
| Generator registry failures
|--------------------------------------------------------------------------
*/

it('records an error when no generator exists for the requested language', function () {
    $request = request();

    $this->registry
        ->shouldReceive('get')
        ->once()
        ->with('typescript')
        ->andReturnNull();

    $this->resolver->shouldNotReceive('resolve');
    $this->parser->shouldNotReceive('parse');
    $this->generator->shouldNotReceive('generate');
    $this->formatter->shouldNotReceive('format');
    $this->writer->shouldNotReceive('write');

    $result = $this->service->execute($request);

    expect($result->getGeneratedTypes())->toBeEmpty()
        ->and($result->getGeneratedFiles())->toBeEmpty()
        ->and($result->getErrors())->toHaveCount(1)
        ->and($result->getErrors()[0]->stage)->toBe(GenerationStage::Resolving)
        ->and($result->getErrors()[0]->className)->toBeNull()
        ->and($result->getErrors()[0]->exception)->toBeInstanceOf(UnsupportedLanguageException::class);

});

/*
|--------------------------------------------------------------------------
| Source resolution failures
|--------------------------------------------------------------------------
*/

it('records a source resolution failure and stops processing', function () {
    $request = request();

    $exception = new RuntimeException('Unable to resolve source');

    $this->registry
        ->shouldReceive('get')
        ->once()
        ->with('typescript')
        ->andReturn($this->generator);

    $this->resolver
        ->shouldReceive('resolve')
        ->once()
        ->with('App\\Enums')
        ->andThrow($exception);

    $this->parser->shouldNotReceive('parse');
    $this->generator->shouldNotReceive('generate');
    $this->formatter->shouldNotReceive('format');
    $this->writer->shouldNotReceive('write');

    $result = $this->service->execute($request);

    expect($result->getGeneratedTypes())->toBeEmpty()
        ->and($result->getGeneratedFiles())->toBeEmpty()
        ->and($result->getErrors())->toHaveCount(1)
        ->and($result->getErrors()[0]->stage)->toBe(GenerationStage::Resolving)
        ->and($result->getErrors()[0]->className)->toBeNull()
        ->and($result->getErrors()[0]->exception)->toBe($exception);

});
