<?php

namespace Look\Workflows\Core\Steps;

use Look\Workflows\Core\Concerns\EvaluatesExpressions;
use Look\Workflows\Core\Contracts\Deterministic;
use Look\Workflows\Core\Runtime\ExecutionResult;
use Look\Workflows\Core\Support\FluentData;

class Filter extends AbstractStep implements Deterministic
{
    use EvaluatesExpressions;

    protected ?string $path = null;
    protected ?string $condition = null;

    // INSTANTIATION

    public static function make(?string $condition = null, ?string $path = null): self
    {
        $step = new self;

        if ($condition) {
            $step->filter($condition, $path);
        }

        return $step;
    }

    // PARAMETERS

    public function filter(string $condition, ?string $path = null): self
    {
        $this->condition = $condition;
        $this->path = $path;

        return $this;
    }

    // EXECUTION

    public function apply(?FluentData $data = null, string $port = 'input')
    {
        if (
            is_null($this->condition)
            || is_null($data)
        ) {
            return;
        }

        if ($this->path) {
            $target = $data->get($this->path);
        } else {
            $target = $data->toArray();
        }

        $wasArray = is_array($target);
        if (!$wasArray) {
            $target = [$target];
        }

        if ($this->condition) {
            $filtered = array_filter(
                $target,
                function ($item, $key) {
                    return $this->evaluate($this->condition, ['key' => $key, 'item' => $item]);
                },
                ARRAY_FILTER_USE_BOTH
            );
        } else {
            $filtered = [];
        }

        if ($this->path) {
            if ($wasArray) {
                data_set($data, $this->path, $filtered);
            } else {
                if (empty($filtered)) {
                    data_forget($data, $this->path);
                } else {
                    data_set($data, $this->path, current($filtered));
                }
            }
        } else {
            $data = $filtered;
        }

        if (is_scalar($data)) {
            $data = [$data];
        }

        return ExecutionResult::output($data);
    }

    // SERIALIZATION

    public function import(array $data): void
    {
        $this->path = $data['path'] ?? null;
        $this->condition = $data['condition'] ?? null;
    }

    public function export(): array
    {
        return array_filter([
            'path' => $this->path ?? null,
            'condition' => $this->condition ?? null,
        ]);
    }
}
