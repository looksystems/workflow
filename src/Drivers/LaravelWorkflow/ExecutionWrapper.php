<?php

namespace Look\Workflows\Drivers\LaravelWorkflow;

use Look\Workflows\Core\Exceptions\InvalidDirection;
use Look\Workflows\Core\Exceptions\StepNotFound;
use Look\Workflows\Core\Runtime\Execution;
use Workflow\ActivityStub;
use Workflow\Workflow as DriverWorkflow;

class ExecutionWrapper extends DriverWorkflow
{
    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    public function execute(Execution $execution)
    {
        foreach ($execution->run() as $call) {
            yield ActivityStub::make(StepWrapper::class, $call);
        }
    }
}
