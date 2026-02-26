<?php

namespace Look\Workflows\Core\Contracts;

use Look\Workflows\Core\Runtime\Execution;

interface ExecutionDriver
{
    public function queue(Execution $execution): void;
}
