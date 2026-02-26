<?php

namespace Look\Workflows\Core\Steps;

use Look\Workflows\Core\Concerns\EvaluatesExpressions;
use Look\Workflows\Core\Contracts\Deterministic;
use Look\Workflows\Core\Runtime\ExecutionResults;
use Look\Workflows\Core\Support\FluentData;

class Conditional extends AbstractStep implements Deterministic
{
    use EvaluatesExpressions;

    protected array $conditions = [];
    protected ?string $default;

    // INSTANTIATION

    public static function make(array $conditions = []): self
    {
        $step = new self;

        if ($conditions) {
            $step->addConditions($conditions);
        }

        return $step;
    }

    // PARAMETERS

    public function addConditions(array $conditions): self
    {
        $this->conditions = array_merge($this->conditions, $conditions);

        return $this;
    }

    public function addCondition(string $port, string $condition): self
    {
        $this->conditions[$port] = $condition;

        return $this;
    }

    public function default(string $port): self
    {
        $this->default = $port;

        return $this;
    }

    // EXECUTION

    public function apply(?FluentData $data = null, string $port = 'input')
    {
        $results = ExecutionResults::make();

        foreach ($this->conditions as $port => $condition) {
            if ($data->evaluate($condition)) {
                $results->output($data, $port);
            }
        }

        if ($results->empty() && $this->default) {
            $results->output($data, $this->default);
        }

        return $results;
    }

    // SERIALIZATION

    public function import(array $data): void
    {
        $this->conditions = $data['conditions'] ?? [];
        $this->default = $data['default'] ?? null;
    }

    public function export(): array
    {
        return array_filter([
            'conditions' => $this->conditions,
            'default' => $this->default ?? null,
        ]);
    }
}
