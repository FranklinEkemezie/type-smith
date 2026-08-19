<?php

declare(strict_types=1);

namespace TypeSmith\Console;

use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;
use TypeSmith\GenerationPipeline\GenerationResult;

class GenerationResultPresenter
{
    public function present(GenerationResult $result, OutputInterface $output): void
    {
        $output->writeln('');
        $output->writeln('<info>Type Smith</info>');
        $output->writeln('');

        $output->writeln('<info>✓</info> Generated '.Str::plural('type', $result->generatedCount(), true));
        when((bool) $result->formattedCount(), fn () => $output->writeln('<info>✓</info> Formatted '.Str::plural('type', $result->formattedCount(), true)));
        $output->writeln('<info>✓</info> Written '.Str::plural('file', $result->writtenCount(), true));

        $this->displayFiles($result, $output);

        if ($result->failedCount() > 0) {
            $this->displayErrors($result, $output);
        }

        $output->writeln('');

        if ($result->isSuccessful()) {
            $output->writeln('<info>Generation completed successfully.</info>');
        } else {
            $output->writeln('<error>Generation completed with errors.</error>');
        }

        $output->writeln('');
    }

    private function displayFiles(GenerationResult $result, OutputInterface $output): void
    {
        if ($result->successfulCount() === 0) {
            return;
        }

        $output->writeln('');

        foreach ($result->getGeneratedFiles() as $file) {
            $output->writeln($file->path);
        }
    }

    private function displayErrors(GenerationResult $result, OutputInterface $output): void
    {
        $output->writeln('');

        $output->writeln('<error>✗</error> Failed '.Str::plural('type', $result->failedCount(), true));

        $output->writeln('');
        $output->writeln('<error>Failed: </error>');

        foreach ($result->getErrors() as $error) {
            $output->writeln('    '.$error->className);
            $output->writeln('');
            $output->writeln('    '.$error->stage->label().' failed:');
            $output->writeln('    '.$error->exception->getMessage());
            $output->writeln('');
        }

        $output->writeln('');
    }
}
