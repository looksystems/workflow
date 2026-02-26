<?php

namespace Look\Workflows\Core\Concerns;

trait HasVersion
{
    protected ?int $version = null;

    // VERSION

    public function version(?int $version = null): self|int
    {
        if (!isset($version)) {
            return $this->getVersion();
        }

        return $this->setVersion($version);
    }

    public function getVersion(): int
    {
        if (!isset($this->version)) {
            $this->version = 0;
        }

        return $this->version;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;

        return $this;
    }
}
