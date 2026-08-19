<?php

declare(strict_types=1);

namespace TypeSmith\Providers;

use Illuminate\Support\ServiceProvider;
use TypeSmith\Console\GenerateTypesCommand;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateTypesCommand::class,
            ]);
        }
    }
}
