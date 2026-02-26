<?php

namespace Look\Workflows\Core\Steps\Concerns;

use Look\Workflows\Core\Workflow;

trait HasWorkflow
{
    protected ?Workflow $workflow = null;

    // WORKFLOW

    public function workflow(): ?Workflow
    {
        return $this->workflow ?? null;
    }

    public function setWorkflow(Workflow $workflow): self
    {
        $this->workflow = $workflow;

        return $this;
    }
}
