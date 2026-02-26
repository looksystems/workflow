<?php

namespace Look\Workflows\Drivers\Temporal;

use Look\Workflows\Core\Runtime\ExecutionCall;
use Look\Workflows\Core\Runtime\ExecutionResults;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

#[ActivityInterface]
class StepActivity
{
    #[ActivityMethod]
    public function executeStep(ExecutionCall $call): ExecutionResults
    {
        return $call->execute();
    }
}
