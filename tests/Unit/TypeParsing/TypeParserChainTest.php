<?php

declare(strict_types=1);

namespace TypeSmith\Tests\Unit\TypeParsing;

use Mockery;
use TypeSmith\Exceptions\UnsupportedClassTypeException;
use TypeSmith\Tests\Fixtures\Enums\Status;
use TypeSmith\TypeDefinitions\EnumTypeDefinition;
use TypeSmith\TypeParsing\TypeParser;
use TypeSmith\TypeParsing\TypeParserChain;

beforeEach(function () {
    $this->parser = Mockery::mock(TypeParser::class);
    $this->chain = new TypeParserChain([$this->parser]);
});

it('uses a parser that supports the class', function () {
    $definition = Mockery::mock(EnumTypeDefinition::class);

    $this->parser
        ->shouldReceive('supports')
        ->once()
        ->with(Status::class)
        ->andReturnTrue();

    $this->parser
        ->shouldReceive('parse')
        ->once()
        ->with(Status::class)
        ->andReturn($definition);

    expect($this->chain->parse(Status::class))->toBe($definition);
});

it('skips parses that do not support the class', function () {
    $unsupportedParser = Mockery::mock(TypeParser::class);
    $supportedParser = Mockery::mock(TypeParser::class);

    $definition = Mockery::mock(EnumTypeDefinition::class);

    $unsupportedParser
        ->shouldReceive('supports')
        ->once()
        ->with(Status::class)
        ->andReturnFalse();

    $unsupportedParser->shouldNotReceive('parse');

    $supportedParser
        ->shouldReceive('supports')
        ->once()
        ->with(Status::class)
        ->andReturnTrue();

    $supportedParser
        ->shouldReceive('parse')
        ->once()
        ->with(Status::class)
        ->andReturn($definition);

    $chain = new TypeParserChain([$unsupportedParser, $supportedParser]);

    expect($chain->parse(Status::class))->toBe($definition);
});

it('uses the first parser that supports the class', function () {
    $firstParser = Mockery::mock(TypeParser::class);
    $secondParser = Mockery::mock(TypeParser::class);

    $definition = Mockery::mock(EnumTypeDefinition::class);

    $firstParser
        ->shouldReceive('supports')
        ->once()
        ->with(Status::class)
        ->andReturnTrue();

    $firstParser
        ->shouldReceive('parse')
        ->once()
        ->with(Status::class)
        ->andReturn($definition);

    $secondParser->shouldNotReceive('supports');
    $secondParser->shouldNotReceive('parse');

    $chain = new TypeParserChain([$firstParser, $secondParser]);

    expect($chain->parse(Status::class))->toBe($definition);
});

it('throws when no parser supports the class', function () {
    $this->parser
        ->shouldReceive('supports')
        ->once()
        ->with(Status::class)
        ->andReturnFalse();

    expect(fn () => $this->chain->parse(Status::class))
        ->toThrow(UnsupportedClassTypeException::class);
});
