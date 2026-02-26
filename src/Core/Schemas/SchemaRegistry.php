<?php

namespace Look\Workflows\Core\Schemas;

use Look\Workflows\Core\Contracts\SchemaStore;
use Look\Workflows\Core\Exceptions\SchemaNotFound;
use Look\Workflows\Core\Schemas\Stores\CombinedStore;
use Look\Workflows\Core\Support\Finders\FinderList;
use Look\Workflows\Core\Support\TypeFinder;

class SchemaRegistry extends FinderList
{
    protected static SchemaRegistry $default;
    protected SchemaStore $fallback;
    protected array $namespaces = [];
    protected array $cache = [];

    // INSTANTIATION

    public function __construct()
    {
        parent::__construct();

        $this->fallback = new CombinedStore;
    }

    public static function default(): SchemaRegistry
    {
        if (!isset(self::$default)) {
            self::$default = new SchemaRegistry;
        }

        return self::$default;
    }

    // SCHEMAS

    public function make(string $schema, array $data = [])
    {
        $StepSchema = $this->find($schema);

        return $StepSchema->create($data);
    }

    public function find(string $schema): StepSchema
    {
        $StepSchema = $this->resolveMappedPath($schema, true);
        if (!$StepSchema) {
            throw new SchemaNotFound("Schema [$schema] not found");
        }

        return $this->resolveSchema($StepSchema);
    }

    public function exists(string $schema): bool
    {
        return $this->resolveMappedPath($schema, true);
    }

    public function list($namespaces = null, $prefixed = false): array
    {
        if (is_null($namespaces)) {
            $namespaces = array_keys($this->namespaces);
        } elseif (!$namespaces) {
            $namespaces = ['default'];
        } elseif (!is_array($namespaces)) {
            $namespaces = [$namespaces];
        }

        $prefixed = $prefixed || count($namespaces) > 1;

        $list = [];
        foreach ($namespaces as $namespace) {
            if (!isset($this->namespaces[$namespace])) {
                continue;
            }

            $found = $this->namespaces[$namespace]->list();
            if (empty($found)) {
                continue;
            }

            if ($prefixed && $namespace !== 'default') {
                $found = array_map(
                    function ($item) use ($namespace) {
                        return TypeFinder::encodeNamespaceAndType($item, $namespace);
                    },
                    $found
                );
            }

            $list = array_merge($list, $found);
        }

        return $list;
    }

    protected function checkExists($key): bool
    {
        $found = $this->resolveSchema($key);

        return isset($found);
    }

    protected function resolveSchema($key, bool $useCache = true): ?StepSchema
    {
        if ($useCache && array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        [$namespace, $schema] = TypeFinder::extractNamespaceAndType($key);
        if ($this->isNamespaceRegistered($namespace)) {
            $found = $this->namespace($namespace)->load($schema, false);
        }
        if (empty($found)) {
            $found = $this->fallback->load($key, false);
        }

        $this->cache[$key] = $found;

        return $found;
    }

    // REGISTRY

    public function addSchemas($schemas, $priority = 0, ?string $namespace = null): self
    {
        $this->namespace($namespace)->addSchemas($schemas, $priority);

        return $this;
    }

    public function addPath($path, $priority = 0, ?string $namespace = null): self
    {
        $this->namespace($namespace)->addPath($path, $priority);

        return $this;
    }

    // NAMESPACES

    public function namespace(?string $namespace = null, bool $createIfNotFound = true): SchemaStore
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        if (!isset($this->namespaces[$namespace])) {
            if (!$createIfNotFound) {
                throw new SchemaNotFound("Schema namespace [$namespace] not found");
            }
            $this->namespaces[$namespace] = new CombinedStore;
        }

        return $this->namespaces[$namespace];
    }

    public function registerNamespace(string $namespace): self
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        if (!isset($this->namespaces[$namespace])) {
            $this->namespaces[$namespace] = new CombinedStore;
        }

        return $this;
    }

    public function dropNamespace(string $namespace): self
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        unset($this->namespaces[$namespace]);

        return $this;
    }

    public function isNamespaceRegistered(string $namespace): bool
    {
        return isset($this->namespaces[$namespace]);
    }

    // FALLBACK

    public function fallback(): SchemaStore
    {
        return $this->fallback;
    }

    public function registerCoreSchemas()
    {
        $libPath = realpath(__DIR__.'/../..');

        /*
                $store = (new PhpStore())
                    ->registerPrefix('', 'Mvp\\Elements', $libPath.'/Elements')
                    ->registerPrefix('forms:', 'Mvp\\Form', $libPath.'/Form')
                    ->registerPrefix('fields:', 'Mvp\\Fields', $libPath.'/Fields')
                    ->registerPrefix('charts:', 'Mvp\\Charts', $libPath.'/Charts')
                    ->registerPrefix('nav:', 'Mvp\\Navigation', $libPath.'/Navigation');

                $this->fallback()->addSchemas($store, priority: PHP_INT_MAX);
        */

        return $this;
    }
}
