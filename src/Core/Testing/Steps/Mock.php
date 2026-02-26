<?php

namespace Look\Workflows\Core\Testing\Steps;

use Exception;
use Look\Workflows\Core\Runtime\ExecutionResult;
use Look\Workflows\Core\Steps\AbstractStep;
use Look\Workflows\Core\Support\FluentData;

class Mock extends AbstractStep
{
    protected string $typeNamespace = 'test';

    protected bool $deterministic = true;
    protected array $return = [];
    protected bool $repeat = true;

    protected ?array $pending = null;
    protected array $called = [];

    // INSTANTIATION

    public static function make(bool $deterministic = true): self
    {
        return new self($deterministic);
    }

    public function __construct(?bool $deterministic = null)
    {
        if (isset($deterministic)) {
            $this->deterministic = $deterministic;
        }
    }

    // MOCK

    public function deterministic(?bool $state = bool): mixed
    {
        if (is_null($state)) {
            return $this->deterministic;
        }

        $this->deterministic = $state;

        return $this;
    }

    public function setDeterministic(bool $state = true): self
    {
        $this->deterministic = $state;

        return $this;
    }

    public function isDeterministic(): bool
    {
        return $this->deterministic;
    }

    public function return($value, bool $append = false): self
    {
        if (!$append) {
            $this->return = [];
        }

        $this->return[] = $value;

        return $this;
    }

    public function output(array $data, string $port = 'output', bool $append = false): self
    {
        return $this->return(ExecutionResult::output($data, $output), $append);
    }

    public function error(Exception|string $error, string $port = 'error', bool $append = false): self
    {
        return $this->return(ExecutionResult::error($error, $port), $append);
    }

    public function throw(Exception $exception, bool $append = false): self
    {
        if (!$append) {
            $this->return = [];
        }

        $this->return[] = $exception;

        return $this;
    }

    public function repeat(?bool $state = null): self
    {
        if (is_null($repeat)) {
            return $this->repeat;
        }

        $this->repeat = $state;

        return $this;
    }

    public function called(): array
    {
        return $this->called;
    }

    public function wasCalled(): bool
    {
        return !empty($this->called);
    }

    public function reset(): self
    {
        $this->pending = null;
        $this->called = [];

        return $this;
    }

    // EXECUTION

    public function apply(?FluentData $data = null, string $port = 'input')
    {
        if (!isset($this->pending)) {
            $this->pending = $this->return;
        }

        if ($this->pending) {
            $return = array_pop($this->pending);
            if (empty($this->pending) && $this->repeat) {
                $this->pending = null;
            }
        } else {
            $return = null;
        }

        $this->called[] = ['data' => $data, 'port' => $port, 'return' => $return];

        if ($return instanceof Exception) {
            throw $return;
        }

        if (isset($return)) {
            return $return;
        }
    }

    // SERIALIZATION

    public function import(array $data): void
    {
        $this->deterministic = $data['deterministic'] ?? true;
        $this->return = $data['return'] ?? [];
        $this->repeat = $data['repeat'] ?? true;
    }

    public function export(): array
    {
        return array_filter([
            'deterministic' => $this->deterministic ? null : false,
            'return' => $this->return ?? null,
            'repeat' => $this->repeat ?? null,
        ]);
    }
}
