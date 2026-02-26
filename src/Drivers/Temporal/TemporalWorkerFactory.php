<?php

namespace Look\Workflows\Drivers\Temporal;

use Temporal\WorkerFactory;
use Temporal\Worker\WorkerOptions;

class TemporalWorkerFactory
{
    public static function create(
        string $taskQueue = 'default',
        ?WorkerOptions $workerOptions = null
    ): WorkerFactory {
        $factory = WorkerFactory::create();
        
        $worker = $factory->newWorker(
            $taskQueue,
            $workerOptions ?? WorkerOptions::new()
        );

        // Register the workflow and activity
        $worker->registerWorkflowTypes(ExecutionWorkflow::class);
        $worker->registerActivity(StepActivity::class);

        return $factory;
    }

    public static function createAndRun(
        string $taskQueue = 'default',
        ?WorkerOptions $workerOptions = null
    ): void {
        $factory = self::create($taskQueue, $workerOptions);
        $factory->run();
    }
}
