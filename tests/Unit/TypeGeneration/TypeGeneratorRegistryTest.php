<?php

declare(strict_types=1);

namespace Tests\Unit\TypeGeneration\TypeGeneratorRegistry;

use Mockery;
use TypeSmith\TypeGeneration\TypeGenerator;
use TypeSmith\TypeGeneration\TypeGeneratorRegistry;

beforeEach(function () {
    $this->registry = new TypeGeneratorRegistry;
});

it('registers a type generator for a language', function () {
    $generator = Mockery::mock(TypeGenerator::class);

    $this->registry->register('typescript', $generator);

    expect($this->registry->get('typescript'))->toBe($generator);
});

it('returns the registered generator for a language', function () {
    $typescriptGenerator = Mockery::mock(TypeGenerator::class);
    $kotlinGenerator = Mockery::mock(TypeGenerator::class);

    $this->registry
        ->register('typescript', $typescriptGenerator)
        ->register('kotlin', $kotlinGenerator);

    expect($this->registry->get('typescript'))->toBe($typescriptGenerator)
        ->and($this->registry->get('kotlin'))->toBe($kotlinGenerator);
});

it('returns null when no generator is registered for the language', function () {
    expect($this->registry->get('typescript'))->toBeNull();
});

it('replaces an existing generator when registered again for the same language', function () {
    $firstGenerator = Mockery::mock(TypeGenerator::class);
    $secondGenerator = Mockery::mock(TypeGenerator::class);

    $this->registry->register('typescript', $firstGenerator);

    expect($this->registry->get('typescript'))->toBe($firstGenerator);

    $this->registry->register('typescript', $secondGenerator);

    expect($this->registry->get('typescript'))->toBe($secondGenerator);
});
