<?php

namespace Look\Workflows\Core\Contracts;

use Look\Workflows\Core\Workflow;

interface CanApplyLinks
{
    public function applyLinks(Workflow $workflow): self;
}
