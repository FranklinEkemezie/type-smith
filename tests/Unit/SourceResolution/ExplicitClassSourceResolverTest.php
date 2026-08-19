<?php

declare(strict_types=1);

namespace TypeSmith\Tests\Unit\SourceResolution;

use TypeSmith\SourceResolution\ExplicitClassSourceResolver;
use TypeSmith\Tests\Fixtures\Classes\ExampleClass;
use TypeSmith\Tests\Fixtures\Enums\Status;

beforeEach(function () {
    $this->resolver = new ExplicitClassSourceResolver;
});

it('supports an existing class', function () {
    expect($this->resolver->supports(ExampleClass::class))
        ->toBeTrue();
});

it('supports an enum', function () {
    expect($this->resolver->supports(Status::class))
        ->toBeTrue();
});

it('does not support a namespace', function () {
    expect($this->resolver->supports('TypeSmith\\Tests\\Fixtures\\Enums'))
        ->toBeFalse();
});

it('does not support a non-existing class', function () {
    expect($this->resolver->supports('TypeSmith\\Tests\\Fixtures\\Classes\\DoesNotExist'))
        ->toBeFalse();
});

it('resolves an existing class', function () {
    expect($this->resolver->resolve(ExampleClass::class))
        ->toBe([ExampleClass::class]);
});

it('resolves an enum', function () {
    expect($this->resolver->resolve(Status::class))
        ->toBe([Status::class]);
});
