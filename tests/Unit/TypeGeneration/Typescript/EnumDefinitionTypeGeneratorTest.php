<?php

declare(strict_types=1);

namespace TypeSmith\Tests\Unit\TypeGeneration\Typescript;

use Mockery;
use TypeSmith\TypeDefinitions\EnumCase;
use TypeSmith\TypeDefinitions\EnumTypeDefinition;
use TypeSmith\TypeDefinitions\TypeDefinition;
use TypeSmith\TypeGeneration\GeneratedType;
use TypeSmith\TypeGeneration\Typescript\EnumDefinitionTypeGenerator;

beforeEach(function () {
    $this->generator = new EnumDefinitionTypeGenerator;
});

it('supports enum definitions', function () {
    $definition = new EnumTypeDefinition(
        name: 'App\\Enums\\Status',
        backingType: 'string',
        cases: []
    );

    expect($this->generator->supports($definition))->toBeTrue();
});

it('does not support other type definitions', function () {
    $definition = Mockery::mock(TypeDefinition::class);

    expect($this->generator->supports($definition))->toBeFalse();
});

it('generates a typescript type from an enum definition', function () {
    $definition = new EnumTypeDefinition(
        name: 'App\\Enums\\Status',
        backingType: 'string',
        cases: [
            new EnumCase('Draft', 'draft'),
            new EnumCase('Published', 'published'),
            new EnumCase('Archived', 'archived'),
        ],
    );

    $generatedType = $this->generator->generate($definition);

    expect($generatedType)->toBeInstanceOf(GeneratedType::class)
        ->and($generatedType->name)->toBe('Status')
        ->and($generatedType->content)->toBe(
            "export type Status = 'draft' | 'published' | 'archived'"
        )
        ->and($generatedType->target->language)->toBe('typescript')
        ->and($generatedType->target->extension)->toBe('ts');
});

it('generates a union using enum case names for an unbacked enum', function () {
    $definition = new EnumTypeDefinition(
        name: 'App\\Enums\\Direction',
        backingType: null,
        cases: [
            new EnumCase('North', null),
            new EnumCase('South', null),
            new EnumCase('East', null),
            new EnumCase('West', null),
        ],
    );

    $generatedType = $this->generator->generate($definition);

    expect($generatedType)->toBeInstanceOf(GeneratedType::class)
        ->and($generatedType->name)->toBe('Direction')
        ->and($generatedType->content)->toBe(
            "export type Direction = 'North' | 'South' | 'East' | 'West'"
        )
        ->and($generatedType->target->language)->toBe('typescript')
        ->and($generatedType->target->extension)->toBe('ts');
});
