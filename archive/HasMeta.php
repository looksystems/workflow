<?php

namespace Look\Workflows\Core\Concerns;

trait HasMeta
{
    protected array $meta = [];

    // META

    public function meta(?string $name = null, mixed $value = null): mixed
    {
        if (isset($value)) {
            return $this->setMeta($name, $value);
        }

        return $this->getMeta($name);
    }

    public function getMeta(?string $name = null, mixed $default = null): mixed
    {
        if (!isset($name)) {
            return $this->meta;
        }

        return $this->meta[$name] ?? $default;
    }

    public function setMeta(string|array $nameOrList, mixed $value = null): self
    {
        if (is_array($nameOrList)) {
            $this->meta = array_merge($this->meta, $nameOrList);
        } else {
            $this->meta[$nameOrList] = $value;
        }

        return $this;
    }

    public function dropMeta(array|string|null $name = null): self
    {
        if (!isset($name)) {
            $this->meta = [];
        } elseif (is_array($name)) {
            $this->meta = array_diff_key($this->meta, array_flip($name));
        } else {
            unset($this->meta[$name]);
        }

        return $this;
    }

    public function hasMeta(?string $name = null): bool
    {
        return is_null($name) ? !empty($this->meta) : isset($this->meta[$name]);
    }
}
