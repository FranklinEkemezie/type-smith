<?php

namespace TypeSmith\Tests\Feature;

use Mockery;
use Symfony\Component\Console\Output\OutputInterface;
use TypeSmith\Console\GenerationResultPresenter;
use TypeSmith\Exceptions\Exception;
use TypeSmith\GenerationPipeline\GenerateTypes;
use TypeSmith\GenerationPipeline\GenerationRequest;
use TypeSmith\GenerationPipeline\GenerationResult;
use TypeSmith\GenerationPipeline\GenerationStage;

beforeEach(function () {
    $this->orchestrator = Mockery::mock(GenerateTypes::class);
    $this->presenter = Mockery::mock(GenerationResultPresenter::class);

    $this->app->instance(GenerateTypes::class, $this->orchestrator);
    $this->app->instance(GenerationResultPresenter::class, $this->presenter);
});

afterEach(function () {
    Mockery::close();
});

it('creates a generation request from the command arguments and executes it', function () {
    $result = new GenerationResult;

    $this->orchestrator
        ->shouldReceive('execute')
        ->once()
        ->withArgs(fn (GenerationRequest $request) => (
            $request->source === 'App\\Enums' &&
            $request->language === 'typescript' &&
            $request->output === 'resources/js/types' &&
            $request->shouldFormat === false
        ))
        ->andReturn($result);

    $this->presenter
        ->shouldReceive('present')
        ->once()
        ->with($result, Mockery::type(OutputInterface::class));

    $this->artisan('type-smith:generate', [
        'source' => 'App\\Enums',
    ])
        ->assertSuccessful();
});

it('passes the requested language to the generation request', function () {
    $result = new GenerationResult;

    $this->orchestrator
        ->shouldReceive('execute')
        ->once()
        ->withArgs(fn (GenerationRequest $request) => (
            $request->source === 'App\\Enums' &&
            $request->language === 'kotlin'
        ))
        ->andReturn($result);

    $this->presenter
        ->shouldReceive('present')
        ->once();

    $this->artisan('type-smith:generate', [
        'source' => 'App\\Enums',
        '--language' => 'kotlin',
    ])
        ->assertSuccessful();
});

it('passes the requested output directory to the generation request', function () {
    $result = new GenerationResult;

    $this->orchestrator
        ->shouldReceive('execute')
        ->once()
        ->withArgs(fn (GenerationRequest $request) => $request->output === 'resources/js/types')
        ->andReturn($result);

    $this->presenter
        ->shouldReceive('present')
        ->once();

    $this->artisan('type-smith:generate', [
        'source' => 'App\\Enums',
        '--output' => 'resources/js/types',
    ])
        ->assertSuccessful();
});

it('enables formatting when the format option is provided', function () {
    $result = new GenerationResult;

    $this->orchestrator
        ->shouldReceive('execute')
        ->once()
        ->withArgs(fn (GenerationRequest $request) => $request->shouldFormat)
        ->andReturn($result);

    $this->presenter
        ->shouldReceive('present')
        ->once();

    $this->artisan('type-smith:generate', [
        'source' => 'App\\Enums',
        '--format' => true,
    ])
        ->assertSuccessful();
});

it('does not enable formatting when the format option is omitted', function () {
    $result = new GenerationResult;

    $this->orchestrator
        ->shouldReceive('execute')
        ->once()
        ->withArgs(fn (GenerationRequest $request) => ! $request->shouldFormat)
        ->andReturn($result);

    $this->presenter
        ->shouldReceive('present')
        ->once();

    $this->artisan('type-smith:generate', [
        'source' => 'App\\Enums',
    ])
        ->assertSuccessful();
});

it('passes the generation result to the presenter', function () {
    $result = new GenerationResult;

    $this->orchestrator
        ->shouldReceive('execute')
        ->once()
        ->andReturn($result);

    $this->presenter
        ->shouldReceive('present')
        ->once()
        ->with($result, Mockery::type(OutputInterface::class));

    $this->artisan('type-smith:generate', [
        'source' => 'App\\Enums',
    ])
        ->assertSuccessful();
});

it('uses the command output when presenting the generation result', function () {
    $result = new GenerationResult;

    $this->orchestrator
        ->shouldReceive('execute')
        ->once()
        ->andReturn($result);

    $this->presenter
        ->shouldReceive('present')
        ->once()
        ->withArgs(fn (GenerationResult $presentedResult, $output) => (
            $presentedResult === $result && $output instanceof OutputInterface
        ));

    $this->artisan('type-smith:generate', [
        'source' => 'App\\Enums',
    ])
        ->assertSuccessful();
});

it('uses the default language when none is provided', function () {
    $result = new GenerationResult;

    $this->orchestrator
        ->shouldReceive('execute')
        ->once()
        ->withArgs(fn (GenerationRequest $request) => $request->language === 'typescript')
        ->andReturn($result);

    $this->presenter
        ->shouldReceive('present')
        ->once();

    $this->artisan('type-smith:generate', [
        'source' => 'App\\Enums',
    ])
        ->assertSuccessful();
});

it('uses the default output directory when none is provided', function () {
    $result = new GenerationResult;

    $this->orchestrator
        ->shouldReceive('execute')
        ->once()
        ->withArgs(fn (GenerationRequest $request) => $request->output === 'resources/js/types')
        ->andReturn($result);

    $this->presenter
        ->shouldReceive('present')
        ->once();

    $this->artisan('type-smith:generate', [
        'source' => 'App\\Enums',
    ])
        ->assertSuccessful();
});

it('returns a failure exit code when generation contains errors', function () {
    $result = new GenerationResult()
        ->addError(GenerationStage::Parsing, new Exception('Unsupported enum definition'), 'App\\Enums\\Status');

    $this->orchestrator
        ->shouldReceive('execute')
        ->once()
        ->andReturn($result);

    $this->presenter
        ->shouldReceive('present')
        ->once();

    $this->artisan('type-smith:generate', [
        'source' => 'App\\Enums',
    ])
        ->assertExitCode(1);
});
