<?php

namespace Look\Workflows\Core\Concerns;

use Psr\Container\ContainerInterface;

trait UsesContainer
{
    protected ContainerInterface $container;

    // CONTAINER

    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
