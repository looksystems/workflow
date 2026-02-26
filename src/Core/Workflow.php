<?php

namespace Look\Workflows\Core;

use Look\Workflows\Core\Exceptions\InvalidDirection;
use Look\Workflows\Core\Exceptions\StepNotFound;
use Look\Workflows\Core\Schemas\SchemaRegistry;
use Look\Workflows\Core\Schemas\Traits\UsesSchemaRegistry;

class Workflow
{
    use Concerns\HasDescription;
    use Concerns\HasName;
    use Concerns\HasUuid;
    use Concerns\HasVersion;
    use Concerns\ManagesLinks;
    use Concerns\ManagesSteps;
    use Concerns\UsesContainer;
    use UsesSchemaRegistry;

    // INSTANTIATION

    public static function make(array $steps = [], ?SchemaRegistry $registry = null): self
    {
        $workflow = new self;

        if ($registry) {
            $workflow->useSchemaRegistry($registry);
        }

        if ($steps) {
            $workflow->addSteps($steps);
        }

        return $workflow;
    }

    // SERIALIZATION

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    public function fromArray(array $data): self
    {
        if (isset($data['uuid'])) {
            $this->setUuid($data['uuid']);
        }

        if (isset($data['name'])) {
            $this->setName($data['name']);
        }

        if (isset($data['description'])) {
            $this->setDescription($data['description']);
        }

        if (isset($data['version'])) {
            $this->setVersion($data['version']);
        }

        if (isset($data['steps']) && is_array($data['steps'])) {
            $this->addSteps($data['steps']);
        }

        if (isset($data['links']) && is_array($data['links'])) {
            $this->addLinks($data['links']);
        }

        return $this;
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'uuid' => $this->uuid(),
                'name' => $this->name(),
                'description' => $this->description(),
                'version' => $this->version(),
                'steps' => $this->getStepsAsArray(),
                'links' => $this->getLinksAsArray(),
            ],
            function ($item) {
                return !(is_null($item) || (is_array($item) && empty($item)));
            }
        );
    }

    /**
     * @throws InvalidDirection
     * @throws StepNotFound
     */
    public function __unserialize(array $data): void
    {
        $this->fromArray($data);
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }
}
