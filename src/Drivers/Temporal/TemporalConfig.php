<?php

namespace Look\Workflows\Drivers\Temporal;

use Temporal\Client\ClientOptions;
use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Client\WorkflowOptions;

class TemporalConfig
{
    protected string $address;
    protected ?string $namespace;
    protected string $taskQueue;
    protected ?WorkflowOptions $defaultWorkflowOptions;
    protected ?ClientOptions $clientOptions;

    public function __construct(
        string $address = 'localhost:7233',
        ?string $namespace = null,
        string $taskQueue = 'default',
        ?WorkflowOptions $defaultWorkflowOptions = null,
        ?ClientOptions $clientOptions = null
    ) {
        $this->address = $address;
        $this->namespace = $namespace;
        $this->taskQueue = $taskQueue;
        $this->defaultWorkflowOptions = $defaultWorkflowOptions;
        $this->clientOptions = $clientOptions;
    }

    public static function create(): self
    {
        return new self();
    }

    public function withAddress(string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function withNamespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function withTaskQueue(string $taskQueue): self
    {
        $this->taskQueue = $taskQueue;
        return $this;
    }

    public function withDefaultWorkflowOptions(WorkflowOptions $options): self
    {
        $this->defaultWorkflowOptions = $options;
        return $this;
    }

    public function withClientOptions(ClientOptions $options): self
    {
        $this->clientOptions = $options;
        return $this;
    }

    public function createClient(): WorkflowClientInterface
    {
        $serviceClient = ServiceClient::create($this->address);
        
        $clientOptions = $this->clientOptions ?? new ClientOptions();
        
        if ($this->namespace) {
            $clientOptions = $clientOptions->withNamespace($this->namespace);
        }

        return WorkflowClient::create($serviceClient, $clientOptions);
    }

    public function createDriver(): TemporalDriver
    {
        return new TemporalDriver(
            $this->createClient(),
            $this->taskQueue,
            $this->defaultWorkflowOptions
        );
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function getTaskQueue(): string
    {
        return $this->taskQueue;
    }

    public function getDefaultWorkflowOptions(): ?WorkflowOptions
    {
        return $this->defaultWorkflowOptions;
    }

    public function getClientOptions(): ?ClientOptions
    {
        return $this->clientOptions;
    }
}
