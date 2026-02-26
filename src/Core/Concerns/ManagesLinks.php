<?php

namespace Look\Workflows\Core\Concerns;

use Look\Workflows\Core\Exceptions\InvalidDirection;
use Look\Workflows\Core\Exceptions\StepNotFound;
use Look\Workflows\Core\Support\Port;

trait ManagesLinks
{
    protected array $inputs = [];
    protected array $outputs = [];
    protected array $queuedLinks = [];

    // LINKS

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    public function link(Port|string $source, Port|string $destination): self
    {
        return $this->addLink($source, $destination);
    }

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    public function addLink(Port|string $source, Port|string $destination): self
    {
        $this->queuedLinks[] = ['add', $source, $destination];

        return $this;
    }

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    public function addLinks(array $links): self
    {
        foreach ($links as $source => $destinations) {
            $this->queuedLinks[] = ['add', $source, $destinations];
        }

        return $this;
    }

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    public function links(array $links): self
    {
        return $this->addLinks($links);
    }

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    public function removeLink(Port|string $source, Port|string $destination): self
    {
        $this->queuedLinks[] = ['remove', $source, $destination];

        return $this;
    }

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    public function removeLinks(array $links): self
    {
        foreach ($links as $source => $destinations) {
            $this->queuedLinks[] = ['remove', $source, $destinations];
        }

        return $this;
    }

    public function removeAllLinks(): self
    {
        $this->inputs = [];
        $this->outputs = [];
        $this->queuedLinks = [];

        return $this;
    }

    /**
     * @throws StepNotFound
     */
    public function hasLink(Port|string $source, Port|string $destination): bool
    {
        $this->prepareLinks();

        if (!$source instanceof Port) {
            $source = Port::source($source, workflow: $this);
        }

        if (!$destination instanceof Port) {
            $destination = Port::destination($destination, workflow: $this);
        }

        $from = $source->toWorkflow($this)->key();
        $to = $destination->toWorkflow($this)->key();

        return isset($this->outputs[$from][$to]);
    }

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    public function getDestinations(Port|string $source): array
    {
        $this->prepareLinks();

        if (!$source instanceof Port) {
            $source = Port::source($source, workflow: $this);
        }
        $source->assertOutbound();

        $from = $source->toWorkflow($this)->key();

        return $this->outputs[$from] ?? [];
    }

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    public function getSources(Port|string $destination)
    {
        $this->prepareLinks();

        if (!$destination instanceof Port) {
            $destination = Port::destination($destination, workflow: $this);
        }
        $destination->assertInbound();

        $to = $destination->toWorkflow($this)->key();

        return $this->inputs[$to] ?? [];
    }

    // SERIALIZATION

    protected function getLinksAsArray(): array
    {
        $this->prepareLinks();

        return array_map(fn ($destinations) => array_keys($destinations), $this->outputs);
    }

    // HELPERS

    protected function prepareLinks(): void
    {
        while ($this->queuedLinks) {
            [$method, $source, $destination] = array_shift($this->queuedLinks);
            $method .= 'QueuedLink';
            if (is_array($destination)) {
                foreach ($destination as $dst) {
                    $this->$method($source, $dst);
                }
            } else {
                $this->$method($source, $destination);
            }
        }
    }

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    protected function addQueuedLink(Port|string $source, Port|string $destination): self
    {
        [$source, $destination] = $this->validatePorts($source, $destination);

        $from = $source->toWorkflow($this)->key();
        $to = $destination->toWorkflow($this)->key();

        if (!isset($this->outputs[$from])) {
            $this->outputs[$from] = [];
        }

        $this->outputs[$from][$to] = $destination;

        if (!isset($this->inputs[$to])) {
            $this->inputs[$to] = [];
        }
        $this->inputs[$to][$from] = $source;

        return $this;
    }

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    protected function removeQueuedLink(Port|string $source, Port|string $destination): self
    {
        [$source, $destination] = $this->validatePorts($source, $destination);

        $from = $source->toWorkflow($this)->key();
        $to = $destination->toWorkflow($this)->key();

        unset($this->outputs[$from][$to]);
        unset($this->inputs[$to][$from]);

        return $this;
    }

    /**
     * @throws InvalidDirection|StepNotFound
     */
    protected function validatePorts(Port|string $source, Port|string $destination): array
    {
        if (!$source instanceof Port) {
            $source = Port::source($source, workflow: $this);
        }
        $source->assertOutbound();

        if (!$destination instanceof Port) {
            $destination = Port::destination($destination, workflow: $this);
        }
        $destination->assertInbound();

        return [$source, $destination];
    }
}
