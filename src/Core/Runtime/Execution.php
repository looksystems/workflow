<?php

namespace Look\Workflows\Core\Runtime;

use Exception;
use Look\Workflows\Core\Exceptions\InvalidDirection;
use Look\Workflows\Core\Exceptions\StepNotFound;
use Look\Workflows\Core\Support\FluentData;
use Look\Workflows\Core\Support\Port;
use Look\Workflows\Core\Workflow;
use Generator;
use SplDoublyLinkedList;

class Execution
{
    protected Workflow $workflow;
    protected SplDoublyLinkedList $queue;
    protected bool $running = false; // this flag should be a multi-process lock
    protected bool $complete = false; // this flag should work but need to think about edge cases
    protected array $stack = []; // this should become a data structure

    // INSTANTIATION

    public static function make(Workflow $workflow): Execution
    {
        return new self($workflow);
    }

    public function __construct(Workflow $workflow)
    {
        $this->workflow = $workflow;
        $this->queue = new SplDoublyLinkedList;
    }

    // EXECUTION

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    public function signal(Port|string $destination, FluentData|array $data = []): self
    {
        if (!$destination instanceof Port) {
            $destination = Port::destination($destination, workflow: $this->workflow);
        }
        $destination->assertInbound();

        $this->queue->push(ExecutionCall::make($this->workflow, $destination, $data));

        return $this;
    }

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     * @throws Exception
     */
    public function run(): Generator
    {
        if ($this->running || $this->complete) {
            return;
        }

        $this->running = true;

        $this->stack = [];
        while (!$this->queue->isEmpty()) {
            $current = $this->queue->pop();
            if ($current->isDeterministic()) {
                $current->execute();
            } else {
                yield $current;
            }

            $results = $current->results();

            $this->stack[] = [
                'input' => $current,
                'output' => $results,
            ];

            foreach ($results as $result) {
                $source = Port::output($current->step(), $result->port);
                $destinations = $this->workflow->getDestinations($source);

                foreach ($destinations as $destination) {
                    $this->queue->push(ExecutionCall::make($this->workflow, $destination, $result->data));
                }
            }
        }

        $this->running = false;
        $this->complete = true;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

    public function isComplete(): bool
    {
        return $this->complete;
    }

    public function stack(): array
    {
        return $this->stack;
    }

    public function queue(): SplDoublyLinkedList
    {
        return $this->queue;
    }

    public function workflow(): Workflow
    {
        return $this->workflow;
    }

    // SERIALIZATION

    public function toArray(): array
    {
        $queue = [];
        foreach ($this->queue as $item) {
            $queue[] = $item->toArray();
        }

        $stack = [];
        foreach ($this->stack as $item) {
            $stack[] = [
                'output' => $item['output']->toArray(),
                'input' => $item['input']->toArray(),
            ];
        }

        return [
            'workflow' => $this->workflow->toArray(),
            'queue' => $queue,
            'stack' => $stack,
        ];
    }

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    public function fromArray(array $data): self
    {
        if (!isset($this->workflow)) {
            $this->workflow = Workflow::make();
        }

        if ($data['workflow'] ?? []) {
            $this->workflow->fromArray($data['workflow']);
        }

        $this->stack = [];
        foreach ($data['stack'] ?? [] as $item) {
            $this->stack[] = [
                'output' => ExecutionResults::ensure($item['output']),
                'input' => ExecutionCall::make(
                    $this->workflow,
                    Port::make($item['input']['port'], workflow: $this->workflow),
                    $item['input']['data'] ?? []
                ),
            ];
        }

        $this->queue = new SplDoublyLinkedList;
        foreach ($data['queue'] ?? [] as $item) {
            $item = ExecutionCall::make(
                $this->workflow,
                Port::make($item['port'], workflow: $this->workflow),
                $item['data'] ?? []
            );
            $this->queue->push($item);
        }

        return $this;
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    public function __unserialize(array $data): void
    {
        $this->fromArray($data);
    }

}
