<?php

namespace Look\Workflows\Core\Runtime;

use Look\Workflows\Core\Contracts\Step;
use Look\Workflows\Core\Exceptions\StepNotFound;
use Look\Workflows\Core\Support\FluentData;
use Look\Workflows\Core\Support\Port;
use Look\Workflows\Core\Workflow;

class ExecutionCall
{
    protected Workflow $workflow;
    public Port $port;
    public FluentData $data;
    protected ExecutionResults $results;

    public static function make(Workflow $workflow, Port $destination, FluentData|array $data = []): ExecutionCall
    {
        return new self($workflow, $destination, $data);
    }

    public function __construct(Workflow $workflow, Port $destination, FluentData|array $data = [])
    {
        $this->workflow = $workflow;
        $this->data = FluentData::ensure($data);
        $this->port = $destination;
    }

    public function setWorkflow(Workflow $workflow): self
    {
        $this->workflow = $workflow;

        return $this;
    }

    /**
     * @throws StepNotFound
     */
    public function step(): Step
    {
        return $this->workflow->step($this->port->step);
    }

    /**
     * @throws StepNotFound
     */
    public function isDeterministic(): bool
    {
        return $this->step()->isDeterministic();
    }

    /**
     * @throws StepNotFound
     */
    public function execute(): ExecutionResults
    {
        if (!isset($this->results)) {
            $step = $this->step();
            $this->results = $step->execute($this->data, $this->port->name);
        }

        return $this->results;
    }

    public function results(): ?ExecutionResults
    {
        return $this->results ?? null;
    }

    // SERIALIZATION

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize(array $data): void
    {
        $this->fromArray($data);
    }

    public function toArray(): array
    {
        return [
            'port' => $this->port->key(),
            'data' => $this->data->toArray(),
        ];
    }

    /**
     * @throws StepNotFound
     */
    public function fromArray(array $data): self
    {
        $this->port = Port::make($data['port'], workflow: $this->workflow);
        $this->data = FluentData::ensure($data['data']);

        return $this;
    }

}
