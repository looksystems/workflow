<?php

namespace Look\Workflows\Core\Steps;

use Exception;
use Look\Workflows\Core\Concerns\HasName;
use Look\Workflows\Core\Concerns\HasUuid;
use Look\Workflows\Core\Contracts\CanApplyLinks;
use Look\Workflows\Core\Contracts\Deterministic;
use Look\Workflows\Core\Contracts\Step;
use Look\Workflows\Core\Runtime\ExecutionResult;
use Look\Workflows\Core\Runtime\ExecutionResults;
use Look\Workflows\Core\Steps\Concerns\HasLinks;
use Look\Workflows\Core\Steps\Concerns\HasWorkflow;
use Look\Workflows\Core\Support\FluentData;
use Look\Workflows\Core\Support\TypeFinder;

abstract class AbstractStep implements CanApplyLinks, Step
{
    use HasLinks;
    use HasName;
    use HasUuid;
    use HasWorkflow;

    protected string $typeNamespace = '';

    // TYPE

    public function type(): string
    {
        return TypeFinder::typeFromClass($this, $this->typeNamespace);
    }

    // EXECUTION

    public function execute(FluentData|array|string $data = [], string $port = 'input'): ExecutionResults
    {
        try {
            $data = FluentData::ensure($data);
            $result = $this->apply($data, $port);

            if ($result instanceof ExecutionResults) {
                return $result;
            }

            if ($result instanceof ExecutionResult) {
                return ExecutionResults::make()->push($result);
            }

            $results = ExecutionResults::make();
            if (
                is_null($result)
                || $result === true
            ) {
                $results->output($data);
            } elseif (
                is_array($result)
                || $result instanceof FluentData
            ) {
                $results->output($result);
            }

        } catch (Exception $e) {
            $results = ExecutionResults::make()->error($e);
        }

        return $results;
    }

    public function isDeterministic(): bool
    {
        return $this instanceof Deterministic;
    }

    abstract protected function apply(?FluentData $data = null, string $port = 'input');

    // SERIALIZATION

    public function export(): array
    {
        return [];
    }

    public function import(array $data): void {}

    public function toArray(): array
    {
        $data = [];
        $type = $this->type();
        if ($type) {
            $data['type'] = $type;
        } else {
            $data['class'] = get_class($this);
        }
        $data['uuid'] = $this->uuid();
        $data['name'] = $this->name();
        $data['data'] = $this->export();

        return array_filter($data);
    }
}
