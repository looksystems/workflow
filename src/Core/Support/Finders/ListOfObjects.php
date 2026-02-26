<?php

/**
 * @copyright   LOOKsystems Limited
 * @license     MIT
 */

namespace Look\Workflows\Core\Support\Finders;

use Assert\Assert;

/**
 * List of objects
 */
class ListOfObjects
{
    protected array $list = [];

    protected array $stack = [];

    protected bool $sorted = false;

    protected ?string $isInstanceOf = null;

    // INSTANTIATE

    public function __construct(array $objects = [], $priority = 0)
    {
        foreach ($objects as $object) {
            $this->add($object, $priority);
        }
    }

    // LIST

    public function list(): array
    {
        if (!$this->sorted) {
            usort($this->list, fn ($item) => $item['priority']);
            $this->sorted = true;
        }

        return array_map(fn ($item) => $item['object'], $this->list);
    }

    public function add($object, $priority = 0): self
    {
        if (is_array($object)) {
            $object = new ListOfObjects($object);
        }

        if ($this->isInstanceOf) {
            Assert::that($object)->isInstanceOf($this->isInstanceOf);
        }

        array_unshift(
            $this->list,
            [
                'object' => $object,
                'priority' => $priority,
            ]
        );

        $this->sorted = false;

        return $this;
    }

    public function remove($object): self
    {
        $count = count($this->list);

        if (is_array($object)) {
            $this->list = array_filter(
                $this->list,
                function ($item) use ($object) {
                    return in_array($item['object'], $object);
                }
            );
        } else {
            $this->list = array_filter(
                $this->list,
                function ($item) use ($object) {
                    return $item['object'] != $object;
                }
            );
        }

        $this->sorted &= count($this->list) === $count;

        return $this;
    }

    public function has(PathGenerator $object): bool
    {
        foreach ($this->list as $item) {
            if ($item['object'] === $object) {
                return true;
            }
        }

        return false;
    }

    // STACK

    public function push($object, $priority = 0): self
    {
        if (is_array($object)) {
            $object = new ListOfGenerators($object);
        }

        if ($this->isInstanceOf) {
            Assert::that($object)->isInstanceOf($this->isInstanceOf);
        }

        $this->stack[] = $object;

        return $this->add($object, $priority);
    }

    public function pop(): self
    {
        if (!empty($this->stack)) {
            $object = array_pop($this->stack);
            $this->remove($object);
        }

        return $this;
    }
}
