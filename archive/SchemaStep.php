<?php

namespace Look\Workflows\Core\Steps;

use Look\Workflows\Core\Contracts\Step;

class SchemaStep extends AbstractStep
{
    protected ?string $schema;
    protected array $data = [];
    protected Step $proxy;

    // INSTANTIATION

    public static function make(string $schema, array $data = []): self
    {
        return new self($schema, $data);
    }

    public function __construct(string $schema, array $data = [])
    {
        $this->schema = $schema;
        $this->data = $data;
    }

    // PARAMS

    public function schema(): ?string
    {
        return $this->schema;
    }

    public function data(): array
    {
        return $this->data;
    }

    // STEP

    public function type(): ?string
    {
        return null;
    }

    protected function step(): Step
    {
        if (!$this->proxy) {
            $this->proxy = $this->makeStep();
        }

        return $this->proxy;
    }

    protected function makeStep()
    {
        return $this->workflow()->schemas()->make([
            'schema' => $this->schema,
            'uuid' => $this->uuid(),
            'name' => $this->name(),
            'data' => $this->data,
            'meta' => $this->meta(),
        ]);
    }

    // PROXY

    public static function __callStatic(string $method, array $args)
    {
        $staticClass = get_class($this->step());

        return $staticClass::$method(...$args);
    }

    public function __call(string $method, array $args)
    {
        $step = $this->step();
        $response = $step->$method(...$args);

        return $response === $step ? $this : $response;
    }

    // EXECUTION

    protected function execute()
    {
        return $this->step()->execute();
    }

    public function isDeterministic(): bool
    {
        return $this->step()->isDeterministic();
    }

    // SERIALIZATION

    public function export(): array
    {
        return $this->data;
    }

    public function import(array $data): void
    {
        $this->data = $data;
        unset($this->proxy);
    }

    public function toArray(): array
    {
        $data = [];
        $data['schema'] = $this->schema;
        $data['uuid'] = $this->uuid();
        $data['name'] = $this->name();
        $data['data'] = $this->export();
        $data['meta'] = $this->meta();

        return array_filter($data);
    }
}
