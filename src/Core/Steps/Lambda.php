<?php

namespace Look\Workflows\Core\Steps;

use Closure;
use Look\Workflows\Core\Support\FluentData;
use Laravel\SerializableClosure\SerializableClosure;

class Lambda extends AbstractStep
{
    protected Closure $closure;
    protected bool $deterministic = false;

    // INSTANTIATION

    public static function make(?Closure $closure = null, ?bool $deterministic = null): self
    {
        $step = new self;

        if ($closure) {
            $step->call($closure);
        }

        if (isset($deterministic)) {
            $step->deterministic = $deterministic;
        }

        return $step;
    }

    // PARAMETERS

    public function call(Closure $closure, ?bool $deterministic = null): self
    {
        $this->closure = $closure;

        if (isset($deterministic)) {
            $this->deterministic = $deterministic;
        }

        return $this;
    }

    // EXECUTION

    public function apply(?FluentData $data = null, string $port = 'input')
    {
        return call_user_func($this->closure, $data, $port);
    }

    public function isDeterministic(): bool
    {
        return $this->deterministic;
    }

    // SERIALIZATION

    public function import(array $data): void
    {
        if ($data['closure']) {
            $this->closure = unserialize($data['closure'])->getClosure();
        }
    }

    public function export(): array
    {
        return [
            'closure' => serialize(new SerializableClosure($this->closure)),
        ];
    }
}
