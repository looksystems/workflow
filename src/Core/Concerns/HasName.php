<?php

namespace Look\Workflows\Core\Concerns;

trait HasName
{
    protected ?string $name = null;

    // NAME

    public function name(?string $name = null): self|string|null
    {
        if (!isset($name)) {
            return $this->name;
        }

        return $this->setName($name);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
