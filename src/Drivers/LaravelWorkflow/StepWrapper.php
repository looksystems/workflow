<?php

namespace Look\Workflows\Drivers\LaravelWorkflow;

use Look\Workflows\Core\Exceptions\StepNotFound;
use Look\Workflows\Core\Runtime\ExecutionCall;
use Workflow\Activity;

class StepWrapper extends Activity
{
    /**
     * @throws StepNotFound
     */
    public function execute(ExecutionCall $call)
    {
        return $call->execute();
    }
}
