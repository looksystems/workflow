<?php

namespace Tests\Fixtures;

use Look\Workflows\Core\Steps\AbstractStep;
use Look\Workflows\Core\Support\FluentData;

class StepWithoutType extends AbstractStep
{
    public function type(): string
    {
        return '';
    }

    public function apply(?FluentData $data = null, string $port = 'input') {}
}
