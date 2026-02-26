<?php

namespace Look\Workflows\Drivers\Temporal;

use Look\Workflows\Core\Contracts\ExecutionDriver;
use Look\Workflows\Core\Runtime\Execution;
use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Client\WorkflowOptions;

class TemporalDriver implements ExecutionDriver
{
    protected WorkflowClientInterface $client;
    protected string $taskQueue;
    protected ?WorkflowOptions $defaultWorkflowOptions;

    public function __construct(
        ?WorkflowClientInterface $client = null,
        string $taskQueue = 'default',
        ?WorkflowOptions $defaultWorkflowOptions = null
    ) {
        $this->client = $client ?? WorkflowClient::create(
            ServiceClient::create('localhost:7233')
        );
        $this->taskQueue = $taskQueue;
        $this->defaultWorkflowOptions = $defaultWorkflowOptions;
    }

    public function queue(Execution $execution): void
    {
        $workflowOptions = $this->createWorkflowOptions($execution);
        
        $workflowStub = $this->client->newWorkflowStub(
            ExecutionWorkflow::class,
            $workflowOptions
        );

        $workflowStub->execute($execution);
    }

    protected function createWorkflowOptions(Execution $execution): WorkflowOptions
    {
        $workflowId = $execution->workflow()->uuid() ?? 'workflow-' . uniqid();
        
        $options = (new WorkflowOptions())
            ->withWorkflowId($workflowId)
            ->withTaskQueue($this->taskQueue);

        if ($this->defaultWorkflowOptions) {
            // Merge with default options if provided
            $options = $options
                ->withWorkflowExecutionTimeout($this->defaultWorkflowOptions->workflowExecutionTimeout)
                ->withWorkflowRunTimeout($this->defaultWorkflowOptions->workflowRunTimeout)
                ->withWorkflowTaskTimeout($this->defaultWorkflowOptions->workflowTaskTimeout);
        }

        return $options;
    }

    public function setClient(WorkflowClientInterface $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function setTaskQueue(string $taskQueue): self
    {
        $this->taskQueue = $taskQueue;
        return $this;
    }

    public function setDefaultWorkflowOptions(WorkflowOptions $options): self
    {
        $this->defaultWorkflowOptions = $options;
        return $this;
    }
}
