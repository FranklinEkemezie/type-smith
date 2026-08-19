<?php

namespace TypeSmith\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use TypeSmith\Providers\AppServiceProvider;

abstract class TestCase extends BaseTestCase
{
    //

    protected function getPackageProviders($app): array
    {
        return [
            AppServiceProvider::class,
        ];
    }
}
