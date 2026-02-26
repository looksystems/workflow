<?php

namespace Look\Workflows\Core\Schemas\Stores;

use Look\Workflows\Core\Contracts\SchemaStore;
use Look\Workflows\Core\Contracts\StepSchema;
use Look\Workflows\Core\Exceptions\SchemaNotFound;
use Look\Workflows\Core\Support\Finders\ListOfObjects;

class CombinedStore extends ListOfObjects implements SchemaStore
{
    protected ?string $isInstanceOf = SchemaStore::class;

    // TYPE STORE

    /**
     * @throws SchemaNotFound
     */
    public function load(string $name, bool $throwIfNotFound = true): ?StepSchema
    {
        foreach ($this->stores() as $store) {
            $found = $store->load($name, false);
            if ($found) {
                return $found;
            }
        }

        if ($throwIfNotFound) {
            throw new SchemaNotFound("Schema [$name] not found");
        }

        return null;
    }

    public function exists(string $name): bool
    {
        foreach ($this->stores() as $store) {
            $found = $store->exists($name);
            if ($found) {
                return true;
            }
        }

        return false;
    }

    public function list(): array
    {
        $list = [];

        foreach ($this->stores() as $store) {
            $list = array_merge($list, $store->list());
        }

        return array_unique($list);
    }

    // REGISTRY

    public function addSchemas(SchemaStore|array $schemas, int $priority = 0): self
    {
        if (!$schemas instanceof SchemaStore) {
            $schemas = new ArrayStore($schemas);
        }

        return $this->add($schemas, $priority);
    }

    public function addDirectory(string $path, int $priority = 0): self
    {
        return $this->add(new FileStore($path), $priority);
    }

    public function stores(): array
    {
        return parent::list();
    }
}
