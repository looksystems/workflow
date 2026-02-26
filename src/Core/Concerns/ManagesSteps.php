<?php

namespace Look\Workflows\Core\Concerns;

use Look\Workflows\Core\Contracts\CanApplyLinks;
use Look\Workflows\Core\Contracts\Step as StepContract;
use Look\Workflows\Core\Exceptions\StepNotFound;
use Look\Workflows\Core\Step;
use Look\Workflows\Core\Support\Uuid;

trait ManagesSteps
{
    protected array $stepsByUuid = [];
    protected array $stepsByName = [];

    // STEPS

    /**
     * @throws StepNotFound
     */
    public function step(StepContract|array|string $stepUuidOrName): StepContract|self
    {
        if (is_string($stepUuidOrName)) {
            return $this->getStep($stepUuidOrName);
        }

        return $this->addStep($stepUuidOrName);
    }

    public function steps(array $steps): self
    {
        return $this->addSteps($steps);
    }

    /**
     * @throws StepNotFound
     */
    public function getStep(string $stepUuidOrName, bool $throwIfNotFound = true): ?StepContract
    {
        $step = $this->findStep($stepUuidOrName);
        if ($step instanceof StepContract) {
            return $step;
        }

        if (is_array($step)) {
            $step = $this->makeStep($step);
            if ($step) {
                $this->addStep($step);
            }
        }

        if ($step) {
            return $step;
        }

        if ($throwIfNotFound) {
            throw new StepNotFound("Step '$stepUuidOrName' not found");
        }

        return null;
    }

    protected function makeStep(array $step): ?StepContract
    {
        return Step::make($step, $this->schemas() ?? 'default');
    }

    public function addStep(StepContract|array $step): self
    {
        if (is_array($step)) {
            if (empty($step['uuid'])) {
                $step['uuid'] = Uuid::generate();
            }
            $uuid = $step['uuid'];
            $name = $step['name'] ?? null;
            $links = $step['links'] ?? [];
        } elseif ($step instanceof StepContract) {
            $step->setWorkflow($this);
            $uuid = $step->uuid();
            $name = $step->name();
            $links = [];
        }

        $this->stepsByUuid[$uuid] = $step;
        if ($name) {
            $this->stepsByName[$name] = $step;
        }

        if ($step instanceof CanApplyLinks) {
            $step->applyLinks($this);
        }

        if ($links) {
            $this->addLinks($links);
        }

        return $this;
    }

    public function addSteps(array $steps): self
    {
        foreach ($steps as $name => $step) {
            if (!is_numeric($name)) {
                if ($step instanceof StepContract) {
                    $step->setName($name);
                } elseif (is_array($step)) {
                    $step['name'] = $step['name'] ?? $name;
                }
            }

            $this->addStep($step);
        }

        return $this;
    }

    /**
     * @throws StepNotFound
     */
    public function removeStep(StepContract|string $stepUuidOrName, bool $throwIfNotFound = true): self
    {
        if (is_string($stepUuidOrName)) {
            $step = $this->findStep($stepUuidOrName);
            if (!$step) {
                if ($throwIfNotFound) {
                    throw new StepNotFound("Step '$stepUuidOrName' not found");
                }
                return $this;
            }
        }

        if ($stepUuidOrName instanceof StepContract) {
            $uuid = $stepUuidOrName->uuid();
            $name = $stepUuidOrName->name();
        } else {
            $uuid = $step['uuid'] ?? null;
            $name = $step['name'] ?? null;
        }

        // TODO: remove links when removing step
        // $this->removeLinkForStep($step);

        if ($uuid) {
            unset($this->stepsByUuid[$uuid]);
        }

        if ($name) {
            unset($this->stepsByName[$name]);
        }

        return $this;
    }

    /**
     * @throws StepNotFound
     */
    public function removeSteps(array $steps): self
    {
        foreach ($steps as $step) {
            $this->removeStep($step);
        }

        return $this;
    }

    public function removeAllSteps(): self
    {
        $this->stepsByUuid = [];
        $this->stepsByName = [];
        $this->removeAllLinks();

        return $this;
    }

    public function hasStep(StepContract|string $stepUuidOrName): bool
    {
        if ($stepUuidOrName instanceof StepContract) {
            $stepUuidOrName = $stepUuidOrName->uuid();
        }

        return isset($this->stepsByUuid[$stepUuidOrName]) || isset($this->stepsByName[$stepUuidOrName]);
    }

    public function hasSteps(): bool
    {
        return !empty($this->stepsByUuid);
    }

    public function getStepUuid(StepContract|string $stepUuidOrName): ?string
    {
        if ($stepUuidOrName instanceof StepContract) {
            return $stepUuidOrName->uuid();
        }

        $step = $this->findStep($stepUuidOrName);
        if ($step instanceof StepContract) {
            return $step->uuid();
        }

        return $step['uuid'] ?? null;
    }

    protected function findStep(string $uuidOrName): StepContract|array|null
    {
        if (isset($this->stepsByUuid[$uuidOrName])) {
            return $this->stepsByUuid[$uuidOrName];
        }

        if (isset($this->stepsByName[$uuidOrName])) {
            return $this->stepsByName[$uuidOrName];
        }

        return null;
    }

    // SERIALIZATION

    protected function getStepsAsArray(): array
    {
        $steps = [];
        foreach ($this->stepsByUuid as $uuid => $step) {
            $steps[$uuid] = $step instanceof StepContract ? $step->toArray() : $step;
        }

        return $steps;
    }
}
