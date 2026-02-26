<?php

/**
 * @copyright   LOOKsystems Limited
 * @license     MIT
 */

namespace Look\Workflows\Core\Support\Finders;

use Generator;

/**
 * Alias Finder
 */
class AliasFinder implements PathGenerator
{
    protected string $source;

    protected ?string $target;

    // INSTANTIATION

    public static function make(string $source, ?string $target = null): self
    {
        return new self($source, $target);
    }

    public function __construct(string $source, ?string $target = null)
    {
        $this->source = $source;
        $this->target = $target;
    }

    // PATH GENERATOR

    /**
     * Generate paths
     */
    public function generate(string $path): Generator
    {
        if (
            $this->source === $path
            && $this->target
        ) {
            yield $this->target;
        }
    }
}
