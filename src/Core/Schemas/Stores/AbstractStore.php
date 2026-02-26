<?php

namespace Look\Workflows\Core\Schemas\Stores;

use Look\Workflows\Core\Contracts\SchemaStore;
use Look\Workflows\Core\Contracts\StepSchema as StepSchemaContract;
use Look\Workflows\Core\Exceptions\Exception;
use Look\Workflows\Core\Exceptions\InvalidSchema;
use Look\Workflows\Core\Exceptions\SchemaNotFound;
use Look\Workflows\Core\Schemas\StepSchema;
use Illuminate\Support\Arr;

class AbstractStore implements SchemaStore
{
    // SCHEMA STORE

    /**
     * @throws SchemaNotFound
     */
    public function load(string $name, bool $throwIfNotFound = true): ?StepSchemaContract
    {
        $schema = $this->fetch($name);
        if ($schema) {
            return StepSchema::make($schema);
        }

        if ($throwIfNotFound) {
            throw new SchemaNotFound("Schema '$name' not found");
        }

        return null;
    }

    /**
     * @throws SchemaNotFound
     */
    public function exists(string $name): bool
    {
        return !empty($this->load($name, false));
    }

    /**
     * @throws SchemaNotFound
     * @throws InvalidSchema
     * @throws Exception
     */
    public function save($schema, ?string $name = null): self
    {
        if ($schema instanceof StepSchemaContract) {
            if (!$name) {
                $name = $schema->name();
            }
            $schema = $schema->toArray();
        }

        if (!is_array($schema)) {
            throw new InvalidSchema('Invalid schema');
        }

        if (!$name) {
            if (!isset($schema['name'])) {
                throw new SchemaNotFound('Schema not defined');
            }
            $name = $schema['name'];
        }

        $this->store($name, $schema);

        return $this;
    }

    /**
     * @throws Exception
     */
    public function delete($schemaOrName): self
    {
        if ($schemaOrName instanceof StepSchemaContract) {
            $name = $schemaOrName->name();
        } elseif (is_array($schemaOrName)) {
            $name = $schemaOrName['name'] ?? null;
        } else {
            $name = $schemaOrName;
        }

        $this->destroy($name);

        return $this;
    }

    protected function fetch(string $name): ?array
    {
        return null;
    }

    /**
     * @throws Exception
     */
    protected function store(string $name, array $schema)
    {
        throw new Exception('Not supported');
    }

    /**
     * @throws Exception
     */
    protected function destroy(string $name)
    {
        throw new Exception('Not supported');
    }

    public function list(): array
    {
        return [];
    }

    public static function fromNestedArray(array $schemas, string $prefix = '')
    {
        $unnested = [];
        foreach ($schemas as $name => $schema) {
            if (!is_array($schema)) {
                continue;
            }

            $isSchema = false;
            foreach ($schema as $item) {
                if (!is_array($item)) {
                    $isSchema = true;
                    break;
                }
            }

            if ($isSchema) {
                $unnested[$prefix.$name] = $schema;
            } else {
                $unnested = array_merge(
                    $unnested,
                    self::fromNestedArray($schema, $prefix.$name.'.')
                );
            }
        }

        return $unnested;
    }

    public static function toNestedArray(array $schemas, string $prefix = ''): array
    {
        $schemas = Arr::undot($schemas);
        if (!$prefix) {
            return $schemas;
        }

        $data = Arr::undot($prefix);
        data_fill($prefix, $data, $schemas);

        return $data;
    }
}
