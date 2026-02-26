<?php

/**
 * @copyright   LOOKsystems Limited
 * @license     MIT
 */

namespace Look\Workflows\Core\Support\Finders;

use Generator;

/**
 * Path Generator
 */
interface PathGenerator
{
    // PATH GENERATOR

    /**
     * Generate paths
     */
    public function generate(string $path): Generator;
}
