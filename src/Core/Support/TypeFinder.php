<?php

namespace Look\Workflows\Core\Support;

/**
 * @copyright   LOOKsystems Limited
 * @license     MIT
 */
class TypeFinder
{
    public const NAMESPACE_DELIMITER = ':';

    protected array $finders = [];
    protected array $types = [];

    public function resolveClassFromType(string $name): ?string
    {
        if (isset($this->types[$name])) {
            return $this->types[$name];
        }

        foreach ($this->finders as $typePrefix => $finders) {
            $prefixLen = strlen($typePrefix);
            if (strncmp($name, $typePrefix, $prefixLen) !== 0) {
                continue;
            }

            $shortClass = Str::studly(substr($name, $prefixLen));
            if (!$shortClass) {
                continue;
            }

            $finders = array_reverse($finders);
            usort($finders, fn ($a, $b) => $a['priority'] - $b['priority']);
            foreach ($finders as $finder) {
                $class = $finder['phpNamespace'].'\\'.$shortClass;
                if (class_exists($class)) {
                    return $class;
                }
            }
        }

        return null;
    }

    public function registerType(string $name, string $phpClass): self
    {
        $this->types[$name] = $phpClass;

        return $this;
    }

    public function registerPrefix(string $typePrefix, string $phpNamespace, $priority = 0): self
    {
        return $this->appendPrefix($typePrefix, $phpNamespace, $priority);
    }

    public function appendPrefix(string $typePrefix, string $phpNamespace, $priority = 0): self
    {
        if (empty($this->finders[$typePrefix])) {
            $this->finders[$typePrefix] = [];
        }

        $finder = [
            'phpNamespace' => rtrim($phpNamespace, '\\'),
            'priority' => $priority,
        ];

        $this->finders[$typePrefix][] = $finder;

        return $this;
    }

    public function prependPrefix(string $typePrefix, string $phpNamespace, $priority = 0): self
    {
        if (empty($this->finders[$typePrefix])) {
            $this->finders[$typePrefix] = [];
        }

        $finder = [
            'phpNamespace' => rtrim($phpNamespace, '\\'),
            'priority' => $priority,
        ];

        array_unshift($this->finders[$typePrefix], $finder);

        return $this;
    }

    // TYPE NAMES

    public static function typeFromClass(string|object $class, ?string $namespace = null): string
    {
        return self::encodeNamespaceAndType(Str::type($class), $namespace);
    }

    public static function encodeNamespaceAndType(string $type, ?string $namespace = null): string
    {
        if (str_contains($type, self::NAMESPACE_DELIMITER)) {
            return $type;
        }

        return $namespace ? $namespace.self::NAMESPACE_DELIMITER.$type : $type;
    }

    public static function extractNamespaceAndType(string $name, string $defaultNamespace = 'default'): array
    {
        $parts = explode(self::NAMESPACE_DELIMITER, $name, 2);

        return count($parts) === 2 ? $parts : [$defaultNamespace, $name];
    }
}
