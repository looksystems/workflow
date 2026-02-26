<?php

namespace Look\Workflows\Core\Steps\Concerns;

use Closure;
use Look\Workflows\Core\Contracts\DataEvaluator;

trait HasParameters
{
    protected array $parameters = [];

    // PARAMETERS

    public function resolveParameters(array $data, string $port = 'input'): array
    {
        $resolved = [];
        $params = array_merge($this->parameters['*'] ?? [], $this->parameters[$port] ?? []);
        foreach ($params as $name => $value) {
            if ($value instanceof Closure) {
                $value = $value($data, $port, $this);
            }
            if ($value instanceof DataEvaluator) {
                $value = $value->evaluate($data);
            }

            $resolved[$name] = $value;
        }

        return $resolved;
    }

    public function param(string $key, mixed $expression = null): mixed
    {
        if (is_null($expression)) {
            return $this->getParameter($key, $expression);
        }

        return $this->setParameter($key, $expression);
    }

    public function getParameter(string $key, mixed $default = null): mixed
    {
        [$port, $name] = $this->resolvePortAndParam($key);

        $param = $this->parameters[$port][$name] ?? null;
        if (isset($param)) {
            return $param;
        }

        if ($port !== '*') {
            $param = $this->parameters['*'][$name] ?? null;
            if (isset($param)) {
                return $param;
            }
        }

        return $default;
    }

    public function setParameter(string $key, mixed $expression): self
    {
        [$port, $name] = $this->resolvePortAndParam($key);

        if (!isset($this->parameters[$port])) {
            $this->parameters[$port] = [];
        }

        $this->parameters[$port][$name] = $expression;

        return $this;
    }

    public function dropParameter(string $key): self
    {
        [$port, $name] = $this->resolvePortAndParam($key);

        if (!isset($this->parameters[$port])) {
            return $this;
        }

        unset($this->parameters[$port][$name]);

        return $this;
    }

    public function hasParameter(string $key): bool
    {
        [$port, $name] = $this->resolvePortAndParam($key);

        $param = $this->parameters[$port][$name] ?? null;
        if (isset($param)) {
            return true;
        }

        if ($port !== '*') {
            $param = $this->parameters['*'][$name] ?? null;
            if (isset($param)) {
                return true;
            }
        }

        return false;
    }

    public function dropAllParameters(): self
    {
        $this->parameters = [];

        return $this;
    }

    protected function resolvePortAndParam(string $key): array
    {
        $parts = explode(':', $key, 2);

        return isset($parts[1]) ? $parts : ['*', $key];
    }

    protected function getParamtersAsArray(): array
    {
        return [];
    }
}
