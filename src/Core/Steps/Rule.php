<?php

namespace Look\Workflows\Core\Steps;

use Look\Workflows\Core\Concerns\EvaluatesExpressions;
use Look\Workflows\Core\Contracts\Deterministic;
use Look\Workflows\Core\Support\FluentData;

class Rule extends AbstractStep implements Deterministic
{
    use EvaluatesExpressions;

    protected ?string $applyIf = null;
    protected array $actions = [];
    protected array $otherwise = [];

    // INSTANTIATION

    public static function make(): self
    {
        return new self;
    }

    // PARAMETERS

    public function applyIf(string $condition): self
    {
        $this->applyIf = $condition;

        return $this;
    }

    public function when(?string $condition, array $actions): self
    {
        $this->actions[] = ['condition' => $condition, 'actions' => $actions];

        return $this;
    }

    public function actions(array $actions): self
    {
        $this->actions[] = ['actions' => $actions];

        return $this;
    }

    public function otherwise(array $actions): self
    {
        $this->otherwise = array_merge($this->otherwise, $actions);

        return $this;
    }

    // EXECUTION

    public function apply(?FluentData $data = null, string $port = 'input')
    {
        if ($this->applyIf) {
            if (!$data->evaluate($this->applyIf)) {
                return;
            }
        }

        $matches = 0;
        foreach ($this->actions as $rule) {
            if (isset($rule['condition'])) {
                $matched = $rule['condition'] ? $this->evaluate($rule['condition']) : false;
            } else {
                $matched = true;
            }
            if (!$matched) {
                continue;
            }

            $matches++;
            foreach ($rule['actions'] ?? [] as $path => $expression) {
                if (is_numeric($path)) {
                    continue;
                }

                $value = $data->evaluate($expression);
                $data->set($path, $value);
            }
        }

        if (!$matches) {
            foreach ($this->otherwise as $path => $expression) {
                if (is_numeric($path)) {
                    continue;
                }

                $value = $data->evaluate($expression);
                $data->set($path, $value);
            }
        }
    }

    // SERIALIZATION

    public function import(array $data): void
    {
        $this->applyIf = $data['applyIf'] ?? null;
        $this->actions = $data['actions'] ?? [];
        $this->otherwise = $data['otherwise'] ?? [];
    }

    public function export(): array
    {
        return array_filter([
            'applyIf' => $this->applyIf,
            'actions' => $this->actions,
            'otherwise' => $this->otherwise,
        ]);
    }
}
