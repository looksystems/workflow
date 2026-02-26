<?php

namespace Look\Workflows\Core\Steps\Concerns;

use Look\Workflows\Core\Exceptions\InvalidDirection;
use Look\Workflows\Core\Exceptions\StepNotFound;
use Look\Workflows\Core\Support\Port;
use Look\Workflows\Core\Workflow;

trait HasLinks
{
    protected array $links = [];

    // LINKS

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    public function link(Port|string $destination, string $source = 'output'): self
    {
        $port = Port::output($this, $source);

        $workflow = $this->workflow();
        if ($workflow) {
            $workflow->addLink($port, $destination);
            return $this;
        }

        $key = $port->key();
        if (!isset($this->links[$key])) {
            $this->links[$key] = [];
        }

        $this->links[$key][] = Port::input($destination)->key();

        return $this;
    }

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    public function links(array $links): self
    {
        foreach ($links as $source => $destination) {
            $this->link($destination, is_numeric($source) ? 'output' : $source);
        }

        return $this;
    }

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    public function applyLinks(Workflow $workflow): self
    {
        if (!empty($this->links)) {
            $workflow->addLinks($this->links);
            $this->links = [];
        }

        return $this;
    }
}
