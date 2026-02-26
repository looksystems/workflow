<?php

namespace Look\Workflows\Laravel;

use Illuminate\Support\ServiceProvider;

class WorkflowServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->bootConfig();
    }

    protected function bootConfig()
    {
        $this->mergeConfigFrom(
            $this->getPackagePath('config/workflows.php'),
            'looksystems.workflows'
        );

        $this->publishes(
            [
                $this->getPackagePath('config/workflows.php') => config_path('looksystems/workflows.php'),
            ],
            'config'
        );
    }

    public function getPackagePath($path = null): string
    {
        return __DIR__.'/'.$path;
    }
}
