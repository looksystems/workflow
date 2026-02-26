<?php

namespace Look\Workflows\Core\Concerns;

trait HasDescription
{
    protected ?string $description = null;

    // NAME

    public function description(?string $description = null): self|string|null
    {
        if (!isset($description)) {
            return $this->description;
        }

        return $this->setDescription($description);
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
