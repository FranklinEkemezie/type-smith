<?php

declare(strict_types=1);

namespace TypeSmith\Tests\Unit\SourceResolution;

use TypeSmith\SourceResolution\NamespaceSourceResolver;
use TypeSmith\Tests\Fixtures\Classes\ExampleClass;
use TypeSmith\Tests\Fixtures\Enums\Direction;
use TypeSmith\Tests\Fixtures\Enums\Nested\PaymentStatus;
use TypeSmith\Tests\Fixtures\Enums\Nested\Priority;
use TypeSmith\Tests\Fixtures\Enums\Status;
use TypeSmith\Tests\Fixtures\Enums\UserRole;

$classLoader = require dirname(__DIR__, 3).'/vendor/autoload.php';

beforeEach(function () use ($classLoader) {
    $this->resolver = new NamespaceSourceResolver($classLoader);
});

it('supports an existing namespace', function () {
    expect($this->resolver->supports('TypeSmith\\Tests\\Fixtures\\Enums\\'))
        ->toBeTrue();
});

it('does not support an explicit class', function () {
    expect($this->resolver->supports(Status::class))
        ->toBeFalse();
});

it('does not support a non-existent namespace', function () {
    expect($this->resolver->supports('TypeSmith\\Tests\\Fixtures\\DoesNotExist'))
        ->toBeFalse();
});

it('resolves all classes in the namespace', function () {
    $classes = $this->resolver->resolve('TypeSmith\\Tests\\Fixtures\\Enums');

    expect($classes)
        ->toEqualCanonicalizing([
            Status::class,
            UserRole::class,
            PaymentStatus::class,
            Priority::class,
            Direction::class,
        ]);
});

it('does not include classes outside the namespace', function () {
    $classes = $this->resolver->resolve('TypeSmith\\Tests\\Fixtures\\Enums');

    expect($classes)->not->toContain([ExampleClass::class]);
});
