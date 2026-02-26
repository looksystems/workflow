<?php

namespace Look\Workflows\Core\Support;

use ArchTech\Enums\Options;

enum PortType: int
{
    use Options;

    case Input = 1;
    case Output = 2;

    public static function fromDir(string $dir): ?PortType
    {
        return match ($dir) {
            'input' => PortType::Input,
            'output' => PortType::Output,
            default => null
        };
    }

    public function dir(): string
    {
        return lcfirst($this->name);
    }
}
