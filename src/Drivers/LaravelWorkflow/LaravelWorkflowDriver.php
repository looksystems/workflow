<?php

namespace Look\Workflows\Drivers\LaravelWorkflow;

use Look\Workflows\Core\Contracts\ExecutionDriver;
use Look\Workflows\Core\Exceptions\StepNotFound;
use Look\Workflows\Core\Runtime\Execution;
use Illuminate\Contracts\Container\BindingResolutionException;
use Workflow\WorkflowStub;

class LaravelWorkflowDriver implements ExecutionDriver
{
    /**
     * @throws BindingResolutionException
     * @throws StepNotFound|InvalidDirection
     */
    public function queue(Execution $execution): void
    {
        $workflow = WorkflowStub::make(ExecutionWrapper::class);
        $workflow->start($execution);
    }
}
