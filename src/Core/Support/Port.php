<?php

namespace Look\Workflows\Core\Support;

use Look\Workflows\Core\Contracts\Step;
use Look\Workflows\Core\Exceptions\InvalidDirection;
use Look\Workflows\Core\Exceptions\StepNotFound;
use Look\Workflows\Core\Workflow;

class Port
{
    public string $step;
    public string $name;
    public PortType $type;

    // INSTANTIATE

    /**
     * @throws StepNotFound
     */
    public static function destination(Step|array|string $stepUuidOrName, ?string $port = null, ?Workflow $workflow = null): Port
    {
        return self::input($stepUuidOrName, $port, $workflow);
    }

    /**
     * @throws StepNotFound
     */
    public static function input(Step|array|string $stepUuidOrName, ?string $port = null, ?Workflow $workflow = null): Port
    {
        if (is_array($stepUuidOrName)) {
            return self::input($stepUuidOrName['step'] ?? null, $stepUuidOrName['port'] ?? null, $workflow);
        }

        if (is_string($stepUuidOrName) && is_null($port)) {
            return self::make($stepUuidOrName, PortType::Input, $workflow);
        }

        return new self(PortType::Input, $stepUuidOrName, $port ?? 'input', $workflow);
    }

    /**
     * @throws StepNotFound
     */
    public static function source(Step|array|string $stepUuidOrName, ?string $port = null, ?Workflow $workflow = null): Port
    {
        return self::output($stepUuidOrName, $port, $workflow);
    }

    /**
     * @throws StepNotFound
     */
    public static function output(Step|array|string $stepUuidOrName, ?string $port = null, ?Workflow $workflow = null): Port
    {
        if (is_array($stepUuidOrName)) {
            return self::output($stepUuidOrName['step'] ?? null, $stepUuidOrName['port'] ?? null, $workflow);
        }

        if (is_string($stepUuidOrName) && is_null($port)) {
            return self::make($stepUuidOrName, PortType::Output, $workflow);
        }

        return new self(PortType::Output, $stepUuidOrName, $port ?? 'output', $workflow);
    }

    /**
     * @throws StepNotFound
     */
    public static function error(Step|array|string $stepUuidOrName, ?string $port = null, ?Workflow $workflow = null): Port
    {
        if (is_array($stepUuidOrName)) {
            return self::error($stepUuidOrName['step'] ?? null, $stepUuidOrName['port'] ?? null, $workflow);
        }

        return new self(PortType::Output, $stepUuidOrName, $port ?? 'error', $workflow);
    }

    /**
     * @throws StepNotFound
     */
    public static function make(string $key, ?PortType $type = null, ?Workflow $workflow = null): Port
    {
        $parts = explode(':', $key, 3);

        $step = $parts[0];
        $name = $parts[2] ?? null;
        if (isset($parts[1])) {
            if ($type && count($parts) === 2) {
                $name = $parts[1];
            } else {
                $type = PortType::fromDir($parts[1]) ?? $type;
            }
        }
        if (!$name) {
            $name = $type?->dir();
        }

        return new self($type, $step, $name, $workflow);
    }

    /**
     * @throws StepNotFound
     */
    protected function __construct(PortType $type, Step|string $stepUuidOrName, string $port, ?Workflow $workflow = null)
    {
        if ($workflow && is_string($stepUuidOrName)) {
            $step = $workflow->getStepUuid($stepUuidOrName);
            if (!$step) {
                throw new StepNotFound("Step '$stepUuidOrName' not found");
            }
        } else {
            $step = $stepUuidOrName;
        }

        if ($step instanceof Step) {
            $step = $step->name() ?: $step->uuid();
        }

        $this->step = $step;
        $this->name = $port;
        $this->type = $type;
    }

    // ASSERTIONS

    public function isInbound(): bool
    {
        return $this->type === PortType::Input;
    }

    /**
     * @throws InvalidDirection
     */
    public function assertInbound(): self
    {
        if ($this->type !== PortType::Input) {
            throw new InvalidDirection;
        }

        return $this;
    }

    public function isOutbound(): bool
    {
        return $this->type === PortType::Output;
    }

    /**
     * @throws InvalidDirection
     */
    public function assertOutbound(): self
    {
        if ($this->type !== PortType::Output) {
            throw new InvalidDirection;
        }

        return $this;
    }

    // WORKFLOW

    /**
     * @throws StepNotFound
     */
    public function toWorkflow(Workflow $workflow): Port
    {
        $step = $workflow->getStepUuid($this->step);
        if (!$step) {
            throw new StepNotFound('Step '.$this->step.' not found');
        }

        return new self($this->type, $step, $this->name);
    }

    // KEY

    public function key(): string
    {
        $type = $this->type->dir();

        return $this->step.':'.$type.($type !== $this->name ? ':'.$this->name : '');
    }
}
