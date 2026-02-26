<?php

namespace Look\Workflows\Core\Contracts;

use Look\Workflows\Core\Runtime\ExecutionResults;
use Look\Workflows\Core\Support\FluentData;

interface Step
{
    public function type(): ?string;
    public function uuid(?string $uuid = null): self|string;
    public function name(?string $name = null): self|string|null;

    public function execute(FluentData|array|string $data = [], string $port = 'input'): ExecutionResults;
    public function isDeterministic(): bool;

    public function export(): array;
    public function import(array $data): void;
}
