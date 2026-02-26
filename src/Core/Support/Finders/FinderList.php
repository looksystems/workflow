<?php

/**
 * @copyright   LOOKsystems Limited
 * @license     MIT
 */

namespace Look\Workflows\Core\Support\Finders;

use Assert\Assert;
use Generator;

abstract class FinderList implements PathGenerator
{
    protected ListOfGenerators $list;

    // INSTANTIATION

    /**
     * Create path finder
     */
    public function __construct()
    {
        $this->list = new ListOfGenerators;
    }

    public function setup(array $options, $priority = 0): self
    {
        if (isset($options['priority'])) {
            $priority = $options['priority'];
        }

        foreach ($options as $type => $value) {
            if (empty($value)) {
                continue;
            }

            if (is_numeric($type)) {
                $this->setup($value, $priority);

                continue;
            }

            if (!is_array($value)) {
                $value = [$value];
            }

            $setupMethod = $this->resolveSetupMethod(strtolower($type));
            if ($setupMethod && method_exists($this, $setupMethod)) {
                $this->$setupMethod($value, priority: $priority);
            }
        }

        return $this;
    }

    protected function resolveSetupMethod(string $type): ?string
    {
        return match ($type) {
            'prefix', 'prefixes' => 'addPrefixes',
            'alias', 'aliases' => 'addAliases',
            default => null,
        };
    }

    // PATH MAPPING

    protected function resolveMappedPath(iterable|string $path, bool $checkUnmapped = false): ?string
    {
        $paths = is_string($path) ? [$path] : $path;

        Assert::that($paths)->isTraversable();

        foreach ($paths as $path) {
            foreach ($this->list->generate($path) as $mapped) {
                if ($this->checkExists($mapped)) {
                    return $mapped;
                }
            }
            if (
                $checkUnmapped
                && $this->checkExists($path)
            ) {
                return $path;
            }
        }

        return null;
    }

    abstract protected function checkExists($path): bool;

    // PREFIXES

    public function addPrefix(string $prefix, $priority = 0): self
    {
        $this->list->add(new PrefixFinder($prefix), $priority);

        return $this;
    }

    public function addPrefixes($prefixes, $priority = 0): self
    {
        Assert::that($prefixes)->isTraversable();

        foreach ($prefixes as $prefix) {
            $this->list->add(new PrefixFinder($prefix), $priority);
        }

        return $this;
    }

    // ALIASES

    public function addAlias(string $source, string $target, $priority = 0): self
    {
        $this->list->add(new AliasFinder($source, $target), $priority);

        return $this;
    }

    public function addAliases($aliases, $priority = 0): self
    {
        Assert::that($aliases)->isTraversable();

        foreach ($aliases as $source => $target) {
            $this->list->add(new AliasFinder($source, $target), $priority);
        }

        return $this;
    }

    public function add($generator, $priority = 0): self
    {
        $this->list->add($generator, $priority);

        return $this;
    }

    // GENERATORS

    /**
     * Generate paths
     */
    public function generate(string $path): Generator
    {
        return $this->list->generate($path);
    }

    /**
     * Get list of generators.
     */
    public function generators(): ListOfGenerators
    {
        return $this->list;
    }
}
