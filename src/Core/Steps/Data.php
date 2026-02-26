<?php

namespace Look\Workflows\Core\Steps;

use Look\Workflows\Core\Contracts\Deterministic;
use Look\Workflows\Core\Runtime\ExecutionResult;
use Look\Workflows\Core\Support\FluentData;

class Data extends AbstractStep implements Deterministic
{
    protected string|array|null $data;

    // INSTANTIATION

    public static function make(array|string|null $data = null): self
    {
        $step = new self;

        if ($data) {
            $step->data($data);
        }

        return $step;
    }

    // PARAMETERS

    public function data(array|string $data): self
    {
        $this->data = $data;

        return $this;
    }

    // EXECUTION

    public function apply(?FluentData $data = null, string $port = 'input')
    {
        $data = is_string($this->data) ? @json_decode($this->data, true) : $this->data;

        if (is_array($data)) {
            return ExecutionResult::output($data);
        } elseif (isset($data)) {
            return ExecutionResult::output(['data' => $data]);
        }

        return ExecutionResult::output([]);
    }

    // SERIALIZATION

    public function export(): array
    {
        return $this->data;
    }

    public function import(array $data): void
    {
        $this->data = $data;
    }
}
