<?php

namespace Look\Workflows\Core\Schemas\Stores;

class ArrayStore extends AbstractStore
{
    protected array $schemas = [];

    // INSTANTIATION

    public function __construct(array $schemas)
    {
        $this->schemas = self::fromNestedArray($schemas);
    }

    // SCHEMA STORE

    protected function fetch(string $name): ?array
    {
        return $this->schemas[$name] ?? null;
    }

    protected function store(string $name, array $schema): self
    {
        $this->schemas[$name] = $schema;

        return $this;
    }

    protected function destroy(string $name): self
    {
        unset($this->schemas[$name]);

        return $this;
    }

    public function list(): array
    {
        return array_keys($this->schemas);
    }
}
