<?php

namespace Look\Workflows\Drivers\Temporal;

use Look\Workflows\Core\Runtime\Execution;
use Temporal\Activity\ActivityOptions;
use Temporal\Common\RetryOptions;
use Temporal\Workflow;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
class ExecutionWorkflow
{
    #[WorkflowMethod]
    public function execute(Execution $execution): void
    {
        $activityOptions = ActivityOptions::new()
            ->withStartToCloseTimeout('60')
            ->withRetryOptions(
                RetryOptions::new()
                    ->withMaximumAttempts(3)
            );

        $stepActivity = Workflow::newActivityStub(
            StepActivity::class,
            $activityOptions
        );

        // Iterate through the execution's run generator
        foreach ($execution->run() as $call) {
            // Execute each step as a Temporal activity
            $stepActivity->executeStep($call);
        }
    }
}
