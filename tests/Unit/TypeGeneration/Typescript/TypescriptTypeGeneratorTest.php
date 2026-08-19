<?php

declare(strict_types=1);

namespace TypeSmith\Tests\Unit\TypeGeneration\Typescript;

use Mockery;
use TypeSmith\Exceptions\UnsupportedTypeDefinitionException;
use TypeSmith\TypeDefinitions\TypeDefinition;
use TypeSmith\TypeGeneration\GeneratedType;
use TypeSmith\TypeGeneration\Typescript\TypeDefinitionTypeGenerator;
use TypeSmith\TypeGeneration\Typescript\TypescriptTypeGenerator;

it('generates a type using the first supporting generator', function () {

    $definition = Mockery::mock(TypeDefinition::class);
    $generatedType = Mockery::mock(GeneratedType::class);

    $firstGenerator = Mockery::mock(TypeDefinitionTypeGenerator::class);

    $firstGenerator
        ->shouldReceive('supports')
        ->once()
        ->with($definition)
        ->andReturnTrue();

    $firstGenerator
        ->shouldReceive('generate')
        ->once()
        ->with($definition)
        ->andReturn($generatedType);

    $secondGenerator = Mockery::mock(TypeDefinitionTypeGenerator::class);

    $secondGenerator->shouldNotReceive('supports');
    $secondGenerator->shouldNotReceive('generate');

    $generator = new TypescriptTypeGenerator([$firstGenerator, $secondGenerator]);

    expect($generator->generate($definition))->toBe($generatedType);
});

it('skips generators that do not support the definition', function () {
    $definition = Mockery::mock(TypeDefinition::class);
    $generatedType = Mockery::mock(GeneratedType::class);

    $unsupportedGenerator = Mockery::mock(TypeDefinitionTypeGenerator::class);
    $unsupportedGenerator
        ->shouldReceive('supports')
        ->once()
        ->with($definition)
        ->andReturnFalse();

    $supportedGenerator = Mockery::mock(TypeDefinitionTypeGenerator::class);
    $supportedGenerator
        ->shouldReceive('supports')
        ->once()
        ->with($definition)
        ->andReturnTrue();

    $supportedGenerator
        ->shouldReceive('generate')
        ->once()
        ->with($definition)
        ->andReturn($generatedType);

    $generator = new TypescriptTypeGenerator([$unsupportedGenerator, $supportedGenerator]);

    expect($generator->generate($definition))->toBe($generatedType);
});

it('throws when no generator supports the definition', function () {
    $definition = Mockery::mock(TypeDefinition::class);

    $firstGenerator = Mockery::mock(TypeDefinitionTypeGenerator::class);
    $secondGenerator = Mockery::mock(TypeDefinitionTypeGenerator::class);

    $firstGenerator
        ->shouldReceive('supports')
        ->once()
        ->with($definition)
        ->andReturnFalse();

    $secondGenerator
        ->shouldReceive('supports')
        ->once()
        ->with($definition)
        ->andReturnFalse();

    $generator = new TypescriptTypeGenerator([$firstGenerator, $secondGenerator]);

    expect(fn () => $generator->generate($definition))
        ->toThrow(UnsupportedTypeDefinitionException::class);
});

it('does not generate through an unsupported generator', function () {
    $definition = Mockery::mock(TypeDefinition::class);

    $generator = Mockery::mock(TypeDefinitionTypeGenerator::class);

    $generator
        ->shouldReceive('supports')
        ->once()
        ->with($definition)
        ->andReturnFalse();

    $generator->shouldNotReceive('generate');

    $typeGenerator = new TypescriptTypeGenerator([$generator]);

    expect(fn () => $typeGenerator->generate($definition))
        ->toThrow(UnsupportedTypeDefinitionException::class);
});
