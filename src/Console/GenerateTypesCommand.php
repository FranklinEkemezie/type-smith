<?php

declare(strict_types=1);

namespace TypeSmith\Console;

use Illuminate\Console\Command;
use TypeSmith\GenerationPipeline\GenerateTypes;
use TypeSmith\GenerationPipeline\GenerationRequest;

class GenerateTypesCommand extends Command
{
    protected $signature = '
    type-smith:generate
    {source : The class or namespace containing the PHP definitions}
    {--output=resources/js/types : The output directory}
    {--language=typescript : The output language}
    {--format : Format generated files}';

    protected $description = 'Generate frontend types from PHP definitions';

    public function handle(GenerateTypes $generateTypes, GenerationResultPresenter $presenter): int
    {
        $source = $this->argument('source');
        if (! is_string($source)) {
            $this->error(sprintf(
                'The source argument must be a string, %s given',
                get_debug_type($source))
            );

            return self::FAILURE;
        }

        $output = $this->option('output');
        if (! is_string($output)) {
            $this->error(sprintf(
                'The output option must be a string, %s given',
                get_debug_type($output)
            ));

            return self::FAILURE;
        }

        $language = $this->option('language');
        if (! is_string($language)) {
            $this->error(sprintf(
                'The language option must be a string, %s given',
                get_debug_type($language)
            ));

            return self::FAILURE;
        }

        $request = new GenerationRequest(
            $source, $language, $output, (bool) $this->option('format')
        );

        $result = $generateTypes->execute($request);

        $presenter->present($result, $this->output);

        return $result->isSuccessful() ? self::SUCCESS : self::FAILURE;
    }
}
