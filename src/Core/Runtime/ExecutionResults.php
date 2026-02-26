<?php

namespace Look\Workflows\Core\Runtime;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Exception;
use Look\Workflows\Core\Support\FluentData;
use IteratorAggregate;
use Traversable;
use UnexpectedValueException;

class ExecutionResults implements ArrayAccess, Countable, IteratorAggregate
{
    protected array $results = [];

    public static function ensure(ExecutionResults|array $results): self
    {
        if ($results instanceof ExecutionResults) {
            return $results;
        }

        return self::make()->fromArray($results);
    }

    public static function make(): self
    {
        return new self;
    }

    // RESULTS

    public function push(array|ExecutionResult $resultOrList): self
    {
        if (!isset($this->results)) {
            $this->results = [];
        }

        if ($resultOrList instanceof ExecutionResult) {
            $this->results[] = $resultOrList;
            return $this;
        }

        foreach ($resultOrList as $result) {
            if (is_array($result)) {
                $result = ExecutionResult::ensure($result);
            }
            if (!$result instanceof ExecutionResult) {
                throw new UnexpectedValueException;
            }

            $this->results[] = $result;
        }

        return $this;
    }

    public function output(FluentData|array $data, string $port = 'output', bool $append = true): self
    {
        if (!$append) {
            $this->results = [];
        }

        $this->results[] = ExecutionResult::output($data, $port);

        return $this;
    }

    public function error(Exception|string $exception, string $port = 'error', bool $append = false): self
    {
        if (!$append || !isset($this->results)) {
            $this->results = [];
        }

        $this->results[] = ExecutionResult::error($exception, $port);

        return $this;
    }

    public function clear(): self
    {
        $this->results = [];

        return $this;
    }

    // ARRAY ACCESS

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->results[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->results[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!$value instanceof ExecutionResult) {
            throw new UnexpectedValueException;
        }

        $this->results[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->results[$offset]);
    }

    // COUNTABLE

    public function count(): int
    {
        return count($this->results);
    }

    public function empty(): bool
    {
        return empty($this->results);
    }

    // ITERATOR

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->results);
    }

    // SERIALIZATION

    public function __unserialize(array $data): void
    {
        $this->fromArray($data);
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        $data = [];
        foreach ($this->results as $result) {
            $data[] = $result->toArray();
        }

        return $data;
    }

    public function fromArray(array $results): self
    {
        foreach ($results as $result) {
            $this->push(
                new ExecutionResult($result['port'] ?? 'output', $result['data'] ?? [])
            );
        }

        return $this;
    }
}
