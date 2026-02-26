<?php

namespace Look\Workflows\Core\Steps;

use Look\Workflows\Core\Concerns\EvaluatesExpressions;
use Look\Workflows\Core\Contracts\Deterministic;
use Look\Workflows\Core\Runtime\ExecutionResults;
use Look\Workflows\Core\Support\FluentData;
use Look\Workflows\Core\Support\Port;

class Loop extends AbstractStep implements Deterministic
{
    use EvaluatesExpressions;

    protected ?string $target = null;
    protected string|bool|null $condition = null;
    protected ?string $port = 'output';
    protected ?string $skip = null;

    // INSTANTIATION

    public static function make(): self
    {
        return new self;
    }

    // PARAMETERS

    public function path(string $path): self
    {
        $this->target = $path;

        return $this;
    }

    public function condition(string $expression): self
    {
        $this->condition = $expression;

        return $this;
    }

    public function port(string $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function skip(string $port): self
    {
        $this->skip = $port;

        return $this;
    }

    // EXECUTION

    public function apply(?FluentData $data = null, string $port = 'input')
    {
        if (!$data) {
            return;
        }

        if ($this->target) {
            $target = $data->get($this->target);
        } else {
            $target = $data->toArray();
        }

        if (!is_iterable($target)) {
            $target = [$target];
        }

        $results = ExecutionResults::make();
        foreach ($target as $key => $item) {
            $condition = $this->condition ?? true;
            if (is_string($condition)) {
                $condition = $this->evaluate($this->condition, ['item' => $item, 'key' => $key]);
            }

            $port = $condition ?: $this->skip;
            if (!$port) {
                continue;
            }

            $port = is_string($port) ? $port : $this->port;

            $results->output(['item' => $item, 'key' => $key], $port);
        }

        return $results;
    }

    // SERIALIZATION

    public function import(array $data): void
    {
        $this->target = $data['target'] ?? null;
        $this->condition = $data['condition'] ?? true;
        $this->port = $data['port'] ?? 'output';
        $this->skip = $data['skip'] ?? null;
    }

    public function export(): array
    {
        $port = $this->port ?? 'output';
        if ($port instanceof Port) {
            $port = $port->toArray();
        }

        $skip = $this->skip ?? null;
        if ($skip instanceof Port) {
            $skip = $skip->toArray();
        }

        return array_filter([
            'target' => $this->target ?? null,
            'condition' => $this->condition ?? null,
            'port' => $port,
            'skip' => $skip,
        ]);
    }
}
