<?php

namespace Look\Workflows\Core\Drivers;

use Look\Workflows\Core\Contracts\ExecutionDriver;
use Look\Workflows\Core\Exceptions\InvalidDirection;
use Look\Workflows\Core\Exceptions\StepNotFound;
use Look\Workflows\Core\Runtime\Execution;
use Illuminate\Contracts\Container\BindingResolutionException;

class SyncDriver implements ExecutionDriver
{
    /**
     * @throws BindingResolutionException
     * @throws StepNotFound|InvalidDirection
     */
    public function queue(Execution $execution): void
    {
        foreach ($execution->run() as $call) {
            $call->execute();
        }
    }
}
