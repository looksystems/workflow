<?php

namespace Look\Workflows\Core\Support;

use Ramsey\Uuid\Uuid as RamseyUuid;

class Uuid
{
    public static function generate(): string
    {
        return (string) RamseyUuid::uuid4();
    }
}
