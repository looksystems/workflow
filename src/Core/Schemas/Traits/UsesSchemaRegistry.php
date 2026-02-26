<?php

namespace Look\Workflows\Core\Schemas\Traits;

use Closure;
use Look\Workflows\Core\Schemas\SchemaRegistry;

trait UsesSchemaRegistry
{
    protected SchemaRegistry|Closure $schemaRegistry;

    // SCHEMA REGISTRY

    public function schemas(): SchemaRegistry
    {
        if (!isset($this->schemaRegistry)) {
            return SchemaRegistry::default();
        }

        if ($this->schemaRegistry instanceof Closure) {
            $this->schemaRegistry = call_user_func($this->schemaRegistry);
        }

        return $this->schemaRegistry;
    }

    public function useSchemaRegistry(SchemaRegistry|Closure $registry): self
    {
        $this->schemaRegistry = $registry;

        return $this;
    }
}
