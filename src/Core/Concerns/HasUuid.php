<?php

namespace Look\Workflows\Core\Concerns;

use Look\Workflows\Core\Support\Uuid;

trait HasUuid
{
    protected ?string $uuid = null;

    // UUID

    public function uuid(?string $uuid = null): self|string
    {
        if (!isset($uuid)) {
            return $this->getUuid();
        }

        return $this->setUuid($uuid);
    }

    public function getUuid(): string
    {
        if (!isset($this->uuid)) {
            $this->uuid = Uuid::generate();
        }

        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }
}
