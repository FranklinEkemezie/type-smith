<?php

namespace Tests\Unit\Console;

use Mockery;
use Symfony\Component\Console\Output\BufferedOutput;
use TypeSmith\Console\GenerationResultPresenter;
use TypeSmith\Exceptions\Exception;
use TypeSmith\GenerationPipeline\GenerationResult;
use TypeSmith\GenerationPipeline\GenerationStage;
use TypeSmith\Output\GeneratedFile;
use TypeSmith\TypeGeneration\GeneratedType;

beforeEach(function () {
    $this->presenter = new GenerationResultPresenter;

    $this->printOutput = new BufferedOutput;
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function generatedType(): GeneratedType
{
    return Mockery::mock(GeneratedType::class);
}

function generatedFile(string $path): GeneratedFile
{
    return new GeneratedFile($path);
}

/*
|--------------------------------------------------------------------------
| Successful generation
|--------------------------------------------------------------------------
*/

it('presents a successful generation result', function () {
    $result = new GenerationResult()
        ->addGeneratedType(generatedType())
        ->addGeneratedType(generatedType())
        ->addGeneratedType(generatedType())
        ->addFormattedType(generatedType())
        ->addFormattedType(generatedType())
        ->addFormattedType(generatedType())
        ->addGeneratedFile(generatedFile('resources/js/types/Status.ts'))
        ->addGeneratedFile(generatedFile('resources/js/types/Role.ts'))
        ->addGeneratedFile(generatedFile('resources/js/types/OrderStatus.ts'));

    $this->presenter->present($result, $this->printOutput);

    expect($this->printOutput->fetch())
        ->toContain('Type Smith')
        ->toContain('✓ Generated 3 types')
        ->toContain('✓ Formatted 3 types')
        ->toContain('✓ Written 3 files')
        ->toContain('resources/js/types/Status.ts')
        ->toContain('resources/js/types/Role.ts')
        ->toContain('resources/js/types/OrderStatus.ts')
        ->toContain('Generation completed successfully.');
});

/*
|--------------------------------------------------------------------------
| Formatting
|--------------------------------------------------------------------------
*/

it('does not display a formatted count when nothing was formatted', function () {
    $result = new GenerationResult()
        ->addGeneratedType(generatedType())
        ->addGeneratedType(generatedType())
        ->addGeneratedFile(generatedFile('resources/js/types/Status.ts'))
        ->addGeneratedFile(generatedFile('resources/js/types/Role.ts'));

    $this->presenter->present($result, $this->printOutput);

    expect($this->printOutput->fetch())
        ->toContain('✓ Generated 2 types')
        ->toContain('✓ Written 2 files')
        ->not->toContain('Formatted 0 types')
        ->toContain('Generation completed successfully.');
});

it('displays the number of successfully formatted types', function () {
    $result = new GenerationResult()
        ->addGeneratedType(generatedType())
        ->addGeneratedType(generatedType())
        ->addGeneratedType(generatedType())
        ->addFormattedType(generatedType())
        ->addFormattedType(generatedType())
        ->addGeneratedFile(generatedFile('resources/js/types/Status.ts'))
        ->addGeneratedFile(generatedFile('resources/js/types/Role.ts'))
        ->addGeneratedFile(generatedFile('resources/js/types/User.ts'));

    $this->presenter->present($result, $this->printOutput);

    expect($this->printOutput->fetch())
        ->toContain('✓ Generated 3 types')
        ->toContain('✓ Formatted 2 types')
        ->toContain('✓ Written 3 files');
});

/*
|--------------------------------------------------------------------------
| File output
|--------------------------------------------------------------------------
*/

it('lists every successfully written file', function () {
    $files = [
        'resources/js/types/Status.ts',
        'resources/js/types/Role.ts',
        'resources/js/types/OrderStatus.ts',
        'resources/js/types/UserRole.ts',
    ];

    $result = new GenerationResult;

    collect($files)->each(fn (string $file) => $result
        ->addGeneratedType(generatedType())
        ->addGeneratedFile(generatedFile($file))
    );

    $this->presenter->present($result, $this->printOutput);

    $output = $this->printOutput->fetch();

    foreach ($files as $file) {
        expect($output)->toContain($file);
    }
});

it('does not display an empty file section when no files were written', function () {
    $result = new GenerationResult;

    $this->presenter->present($result, $this->printOutput);

    expect($this->printOutput->fetch())
        ->not->toContain("\n\n\nGeneration")
        ->toContain('Generation completed successfully.');
});

/*
|--------------------------------------------------------------------------
| Partial failures
|--------------------------------------------------------------------------
*/

it('presents a result containing both successful generation and failures', function () {

    $result = new GenerationResult()
        ->addGeneratedType(generatedType())
        ->addGeneratedType(generatedType())
        ->addGeneratedType(generatedType())
        ->addFormattedType(generatedType())
        ->addFormattedType(generatedType())
        ->addGeneratedFile(generatedFile('resources/js/types/Status.ts'))
        ->addGeneratedFile(generatedFile('resources/js/types/Role.ts'))
        ->addError(GenerationStage::Parsing, new Exception('Unsupported enum definition'), 'App\\Enums\\OrderStatus');

    $this->presenter->present($result, $this->printOutput);

    expect($this->printOutput->fetch())
        ->toContain('✓ Generated 3 types')
        ->toContain('✓ Formatted 2 types')
        ->toContain('✓ Written 2 files')
        ->toContain('✗ Failed 1 type')
        ->toContain('Failed: ')
        ->toContain('App\\Enums\\OrderStatus')
        ->toContain('Parsing')
        ->toContain('Unsupported enum definition')
        ->toContain('Generation completed with errors.');
});

it('presents every generation error', function () {
    $errors = [
        [
            'class' => 'App\\Enums\\Role',
            'stage' => GenerationStage::Parsing,
            'message' => 'Unsupported enum definition',
        ],
        [
            'class' => 'App\\Enums\\Status',
            'stage' => GenerationStage::Generation,
            'message' => 'Unable to generate TypeScript type',
        ],
        [
            'class' => 'App\\Enums\\OrderStatus',
            'stage' => GenerationStage::Writing,
            'message' => 'Unable to write generated file',
        ],
    ];

    $result = new GenerationResult()
        ->addGeneratedType(generatedType())
        ->addGeneratedType(generatedType())
        ->addFormattedType(generatedType())
        ->addGeneratedFile(generatedFile('resources/js/types/Status.ts'));

    foreach ($errors as $error) {
        $result->addError($error['stage'], new Exception($error['message']), $error['class']);
    }

    $this->presenter->present($result, $this->printOutput);

    expect($this->printOutput->fetch())
        ->toContain('✗ Failed 3 types')
        ->toContain('App\\Enums\\Role')
        ->toContain('Parsing')
        ->toContain('Unsupported enum definition')
        ->toContain('App\\Enums\\Status')
        ->toContain('Generation')
        ->toContain('Unable to generate TypeScript type')
        ->toContain('App\\Enums\\OrderStatus')
        ->toContain('Writing')
        ->toContain('Unable to write generated file')
        ->toContain('Generation completed with errors.');
});

/*
|--------------------------------------------------------------------------
| Singular / plural output
|--------------------------------------------------------------------------
*/

it('uses singular wording when exactly one type is generated', function () {
    $result = new GenerationResult()
        ->addGeneratedType(generatedType())
        ->addformattedType(generatedType())
        ->addGeneratedFile(generatedFile('resources/js/types/Status.ts'));

    $this->presenter->present($result, $this->printOutput);

    expect($this->printOutput->fetch())
        ->toContain('✓ Generated 1 type')
        ->toContain('✓ Formatted 1 type')
        ->toContain('✓ Written 1 file')
        ->not->toContain('1 types')
        ->not->toContain('1 files');
});

it('uses singular wording when exactly one type fails', function () {

    $result = new GenerationResult()
        ->addError(GenerationStage::Parsing, new Exception('Unsupported enum definition'), 'App\\Enums\\Status');

    $this->presenter->present($result, $this->printOutput);

    expect($this->printOutput->fetch())
        ->toContain('✗ Failed 1 type')
        ->not->toContain('1 types');
});

/*
|--------------------------------------------------------------------------
| Empty result
|--------------------------------------------------------------------------
*/

it('handles an entirely empty result', function () {
    $result = new GenerationResult;

    $this->presenter->present($result, $this->printOutput);

    expect($this->printOutput->fetch())
        ->toContain('Type Smith')
        ->toContain('✓ Generated 0 types')
        ->toContain('✓ Written 0 files')
        ->toContain('Generation completed successfully.');
});

/*
|--------------------------------------------------------------------------
| Error-only result
|--------------------------------------------------------------------------
*/

it('presents a result where the generation process failed before producing anything', function () {

    $result = new GenerationResult()
        ->addError(GenerationStage::Resolving, new Exception('Unable to resolve source: App\\Enums'));

    $this->presenter->present($result, $this->printOutput);

    expect($this->printOutput->fetch())
        ->toContain('✓ Generated 0 types')
        ->toContain('✓ Written 0 files')
        ->toContain('✗ Failed 1 type')
        ->toContain('App\\Enums')
        ->toContain('Resolving')
        ->toContain('Unable to resolve source')
        ->toContain('Generation completed with errors.');
});

/*
|--------------------------------------------------------------------------
| Ordering
|--------------------------------------------------------------------------
*/

it('presents the generation summary before the file list and completion message', function () {
    $result = new GenerationResult()
        ->addGeneratedType(generatedType())
        ->addFormattedType(generatedType())
        ->addGeneratedFile(generatedFile('resources/js/types/Status.ts'));

    $this->presenter->present($result, $this->printOutput);

    $output = $this->printOutput->fetch();

    $generatedPosition = strpos($output, '✓ Generated 1 type');
    $formattedPosition = strpos($output, '✓ Formatted 1 type');
    $writtenPosition = strpos($output, '✓ Written 1 file');
    $filePosition = strpos($output, 'resources/js/types/Status.ts');
    $completedPosition = strpos($output, 'Generation completed successfully.');

    expect($generatedPosition)->toBeLessThan($formattedPosition)
        ->and($formattedPosition)->toBeLessThan($writtenPosition)
        ->and($writtenPosition)->toBeLessThan($filePosition)
        ->and($filePosition)->toBeLessThan($completedPosition);

});

it('presents failures before the final completion message', function () {

    $result = new GenerationResult()
        ->addError(GenerationStage::Parsing, new Exception('Unsupported enum definition'), 'App\\Enums\\Status');

    $this->presenter->present($result, $this->printOutput);

    $output = $this->printOutput->fetch();

    $failurePosition = strpos($output, 'Failed:');
    $completedPosition = strpos($output, 'Generation completed with errors.');

    expect($failurePosition)->toBeLessThan($completedPosition);
});
