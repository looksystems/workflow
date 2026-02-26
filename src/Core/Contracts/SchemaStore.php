<?php

namespace Look\Workflows\Core\Contracts;

interface SchemaStore
{
    public function load(string $name, bool $throwIfNotFound = true): ?StepSchema;
    public function exists(string $name): bool;
    public function list(): array;
}
