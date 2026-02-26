<?php

namespace Look\Workflows\Core\Schemas;

use Look\Workflows\Core\Contracts\Step;
use Look\Workflows\Core\Contracts\StepSchema as StepSchemaContract;
use Look\Workflows\Core\Exceptions\InvalidStep;

class StepSchema implements StepSchemaContract
{
    protected string $name;
    protected string $stepClass;

    protected ?string $icon = null;
    protected ?string $label = null;
    protected ?string $description = null;
    protected ?string $helpText = null;
    protected array $categories = [];

    protected array $inputs = ['input'];
    protected array $outputs = ['outputs'];
    protected array $errors = ['error'];

    protected array $fields = [];
    protected array $mapping = [];

    // INSTANTIATION

    public static function make(array $definition): StepSchema
    {
        $class = $definition['schemaClass'] ?? StepSchema::class;

        return (new $class)->fromArray($definition);
    }

    protected function __construct() {}

    // SCHEMA

    public function name(): ?string
    {
        return $this->name;
    }

    public function label(): ?string
    {
        if (isset($this->label)) {
            return $this->label;
        }

        if (isset($this->name)) {
            return $this->name;
        }

        return null;
    }

    public function icon(): ?string
    {
        return $this->icon;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function helpText(): ?string
    {
        return $this->helpText;
    }

    public function categories(): array
    {
        return $this->categories;
    }

    // STEP

    /**
     * @throws InvalidStep
     */
    public function step(array $data = []): Step
    {
        $stepClass = $this->definition['stepClass'] ?? null;
        if (!$stepClass) {
            throw new InvalidStep('Invalid step class');
        }

        $step = new $stepClass;

        $this->applyFieldData($step, $data);

        return $step;
    }

    protected function applyFieldData(Step $step, array $data): self
    {
        $mapped = $data;

        /*
        $mapped = [];
        $prefix = $this->mapping['prefix'] ?? '';
        if ($prefix) {
            $prefix = trim($prefix, '.').'.';
        }

        foreach ($data as $name => $value) {
            if (!empty($this->fields[$name]['mapping'])) {
                $path = $this->fields[$name]['mapping'];
            } else {
                $path = $prefix.$name;
            }

            if ($path) {
                data_set($data, $path, $value);
            }
        }
        */

        $step->import($mapped);

        // meta
        // name
        // postfix

        return $this;
    }

    // FIELDS

    public function fields(): array
    {
        return $this->fields;
    }

    // SERIALIZATION

    public function fromArray(array $definition): self
    {
        foreach ($definition as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return array_filter((array) $this);
    }
}
