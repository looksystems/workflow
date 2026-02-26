<?php

namespace Tests;

use Look\Workflows\Laravel\WorkflowServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class LaravelTestCase extends OrchestraTestCase
{
    use RefreshDatabase;
    use WithTestMethods;

    protected function getPackageProviders($app)
    {
        return [
            WorkflowServiceProvider::class,
        ];
    }
}
