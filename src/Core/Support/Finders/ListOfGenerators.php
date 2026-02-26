<?php

/**
 * @copyright   LOOKsystems Limited
 * @license     MIT
 */

namespace Look\Workflows\Core\Support\Finders;

use Generator;

/**
 * List of path generators
 */
class ListOfGenerators extends ListOfObjects implements PathGenerator
{
    protected ?string $isInstanceOf = PathGenerator::class;

    // PATH GENERATOR

    /**
     * Generate paths
     */
    public function generate(string $path): Generator
    {
        $list = $this->list();

        foreach ($list as $generator) {
            foreach ($generator->generate($path) as $mapped) {
                yield $mapped;
            }
        }
    }
}
