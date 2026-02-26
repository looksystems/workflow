<?php

namespace Look\Workflows\Core\Runtime;

use Exception;
use Look\Workflows\Core\Support\FluentData;

class ExecutionResult
{
    public string $port;
    public FluentData $data;

    // INSTANTIATION

    public static function ensure(ExecutionResult|array $result): self
    {
        if ($result instanceof ExecutionResult) {
            return $result;
        }

        return new self(
            $data['port'] ?? 'output',
            FluentData::ensure($data['data'] ?? [])
        );
    }

    public static function output(FluentData|array $data, string $port = 'output'): self
    {
        return new self($port, $data);
    }

    public static function error(Exception|string $exception, string $port = 'error'): self
    {
        $data = self::exceptionToArray($exception);

        return new self($port, $data);
    }

    public function __construct(string $port, FluentData|array $data)
    {
        $this->data = FluentData::ensure($data);
        $this->port = $port;
    }

    // RESULT

    public function setOutput(FluentData|array $data, ?string $port = null): self
    {
        $this->data = FluentData::ensure($data);
        if ($port) {
            $this->port = $port;
        }

        return $this;
    }

    public function setError(Exception|string $exception, ?string $port = null): self
    {
        $this->data = FluentData::ensure(self::exceptionToArray($exception));
        if ($port) {
            $this->port = $port;
        }

        return $this;
    }

    // HELPERS

    public static function exceptionToArray(Exception|string $exception): array
    {
        if ($exception instanceof Exception) {
            return [
                'error' => [
                    'message' => $exception->getMessage(),
                    'class' => get_class($exception),
                ],
            ];
        }

        return [
            'error' => [
                'message' => $exception,
            ],
        ];
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
        return [
            'port' => $this->port,
            'data' => $this->data->toArray(),
        ];
    }

    public function fromArray(array $data): self
    {
        $this->port = $data['port'] ?? 'output';
        $this->data = FluentData::ensure($data['data'] ?? []);

        return $this;
    }

}
