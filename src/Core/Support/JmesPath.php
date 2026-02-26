<?php

namespace Look\Workflows\Core\Support;

use JmesPath\Env;
use JmesPath\SyntaxErrorException;

/**
 * @see https://github.com/jmespath/jmespath.php
 */
class JmesPath
{
    public static function search(string $expression, object|array $data, bool $throwIfSyntaxError = true): mixed
    {
        try {
            $found = Env::search($expression, $data);
        } catch (SyntaxErrorException $e) {
            if ($throwIfSyntaxError) {
                throw $e;
            }

            $found = null;
        }

        return $found;
    }
}
