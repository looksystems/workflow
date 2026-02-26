<?php

/**
 * @copyright   LOOKsystems Limited
 * @license     MIT
 */

namespace Look\Workflows\Core\Support\Finders;

use Generator;

/**
 * Prefix Finder
 */
class PrefixFinder implements PathGenerator
{
    protected string $prefix;

    // INSTANTIATION

    public static function make(string $prefix): self
    {
        return new self($prefix);
    }

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    // PATH GENERATOR

    /**
     * Generate paths
     */
    public function generate(string $path): Generator
    {
        if (
            !str_contains($path, ':')
            && $this->prefix
        ) {
            yield $this->prefix.$path;
        }
    }
}
