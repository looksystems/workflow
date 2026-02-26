<?php

namespace Tests;

use Workflow\Providers\WorkflowServiceProvider;

class LaravelWorkflowTestCase extends LaravelTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            WorkflowServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('cache.default', 'array');
    }
}
