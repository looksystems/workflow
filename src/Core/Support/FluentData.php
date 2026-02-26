<?php

namespace Look\Workflows\Core\Support;

use Countable;
use Look\Workflows\Core\Concerns\EvaluatesExpressions;
use Look\Workflows\Core\Support\Vendor\Illuminate\Fluent;

class FluentData extends Fluent implements Countable
{
    use EvaluatesExpressions;

    public static function make($attributes = []): self
    {
        return new self($attributes);
    }

    public static function ensure($attributes = []): self
    {
        return $attributes instanceof FluentData ? $attributes : new self($attributes);
    }

    public function set($key, $value, $overwrite = true): self
    {
        data_set($this->attributes, $key, $value, $overwrite);

        return $this;
    }

    public function fill($key, $value): self
    {
        data_set($this->attributes, $key, $value, false);

        return $this;
    }

    public function forget($key): self
    {
        data_forget($this->attributes, $key);

        return $this;
    }

    public function query(string $expression, bool $throwIfSyntaxError = true): mixed
    {
        return JmesPath::search($expression, $this->attributes, $throwIfSyntaxError);
    }

    public function transform(array $mapping): self
    {
        $transformed = [];
        foreach ($mapping as $path => $value) {
            data_set($transformed, $path, $this->evaluate($value));
        }

        return new self($transformed);
    }

    /*
        public function transform(array $mapping): self
        {
            $transformed = [];
            foreach ($mapping as $path => $value) {

                data_set($transformed, $path, $value);
            }

            return new self($transformed);
        }

        public function apply(array $mapping): self
        {
            $transformed = [];
            foreach ($mapping as $path => $value) {
                if ($value instanceof Closure) {
                    $value = $value($this, $path);
                }
                if ($value instanceof DataEvaluator) {
                    $value = $value->evaluate($this);
                }

                data_set($transformed, $path, $value);
            }

            return new self($transformed);
        }

        public function resolve($value): mixed
        {
            if ($value instanceof Closure) {
                $value = $value($this, $path);
            }
            if ($value instanceof DataEvaluator) {
                $value = $value->evaluate($this);
            }

            return $value;
        }
    */

    public function count(): int
    {
        return count($this->attributes);
    }

    protected function prepareExpressionContext(array $context): array
    {
        return array_merge($context, $this->attributes);
    }
}
