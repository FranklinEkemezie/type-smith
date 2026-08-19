<?php

declare(strict_types=1);

namespace TypeSmith\Tests\Unit\TypeParsing;

use TypeSmith\Tests\Fixtures\Classes\ExampleClass;
use TypeSmith\Tests\Fixtures\Contracts\ExampleContract;
use TypeSmith\Tests\Fixtures\Enums\Direction;
use TypeSmith\Tests\Fixtures\Enums\Nested\PaymentStatus;
use TypeSmith\Tests\Fixtures\Enums\Nested\Priority;
use TypeSmith\Tests\Fixtures\Enums\Status;
use TypeSmith\Tests\Fixtures\Enums\UserRole;
use TypeSmith\TypeDefinitions\EnumTypeDefinition;
use TypeSmith\TypeParsing\EnumParser;

beforeEach(function () {
    $this->parser = new EnumParser;
});

it('supports a backed enum', function () {
    expect($this->parser->supports(Status::class))->toBeTrue();
});

it('supports an integer backed enum', function () {
    expect($this->parser->supports(Priority::class))->toBeTrue();
});

it('supports an un-backed enum', function () {
    expect($this->parser->supports(Direction::class))->toBeTrue();
});

it('does not support an ordinary class', function () {
    expect($this->parser->supports(ExampleClass::class))->toBeFalse();
});

it('does not support an interface', function () {
    expect($this->parser->supports(ExampleContract::class))->toBeFalse();
});

it('does not support a non-existent class', function () {
    expect($this->parser->supports('TypeSmith\\Tests\\Fixtures\\Enums\\DoesNotExist'))
        ->toBeFalse();
});

it('parses a backed enum into an enum definition', function (string $enumClassName, string $backingType) {
    $enumCases = collect($enumClassName::cases());
    $expectedNames = $enumCases->pluck('name')->toArray();
    $expectedValues = $enumCases->pluck('value')->toArray();

    $definition = $this->parser->parse($enumClassName);

    $definitionCases = collect($definition->cases);
    $names = $definitionCases->pluck('name')->toArray();
    $values = $definitionCases->pluck('value')->toArray();

    expect($definition->name)->toBe($enumClassName)
        ->and($definition->backingType)->toBe($backingType)
        ->and($definition->cases)->toHaveCount($enumCases->count())
        ->and($names)->toEqualCanonicalizing($expectedNames)
        ->and($values)->toEqualCanonicalizing($expectedValues);
})->with([
    [Status::class, 'string'],
    [Priority::class, 'int'],
    [PaymentStatus::class, 'string'],
    [UserRole::class, 'string'],
]);

it('parses an unbacked enum', function () {

    $expectedNames = collect(Direction::cases())->pluck('name')->toArray();

    $definition = $this->parser->parse(Direction::class);

    $definitionCases = collect($definition->cases);
    $names = $definitionCases->pluck('name')->toArray();

    expect($definition)->toBeInstanceOf(EnumTypeDefinition::class)
        ->and($definition->name)->toBe(Direction::class)
        ->and($definition->backingType)->toBeNull()
        ->and($definition->cases)->toHaveCount(count(Direction::cases()))
        ->and($names)->toEqualCanonicalizing($expectedNames);
});
