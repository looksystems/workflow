<?php

namespace Look\Workflows\Laravel\Steps;

use Look\Workflows\Core\Runtime\ExecutionResult;
use Look\Workflows\Core\Steps\AbstractStep;
use Look\Workflows\Core\Support\FluentData;
use Illuminate\Support\Facades\DB;

class Sql extends AbstractStep
{
    protected ?string $connection = null;
    protected ?string $query = null;
    protected array $bindings = [];

    // INSTANTIATION

    public static function make(?string $query = null, ?array $bindings = null, ?string $connection = null): self
    {
        $step = new self;

        if ($query) {
            $step->query($query, $bindings);
        }

        if ($connection) {
            $step->connection($connection);
        }

        return $step;
    }

    // PARAMETERS

    public function connection(string $connection): self
    {
        $this->connection = $connection;

        return $this;
    }

    public function query(string $query, ?array $bindings = null): self
    {
        $this->query = $query;
        if (isset($bindings)) {
            $this->bindings = $bindings;
        }

        return $this;
    }

    public function bind(array $bindings): self
    {
        $this->bindings = $bindings;

        return $this;
    }

    // EXECUTION

    public function apply(?FluentData $data = null, string $port = 'input')
    {
        if (empty($this->query)) {
            return false;
        }

        if ($this->connection) {
            $results = DB::connection($this->connection)
                ->select($this->query, $this->bindings);
        } else {
            $results = DB::select($this->query, $this->bindings);
        }

        return ExecutionResult::output(['data' => $results]);
    }

    // SERIALIALISATION

    public function import(array $data): void
    {
        $this->connection = $data['connection'] ?? null;
        $this->query = $data['query'] ?? null;
        $this->bindings = $data['bindings'] ?? [];
    }

    public function export(): array
    {
        return array_filter([
            'connection' => $this->connection ?? null,
            'query' => $this->query ?? null,
            'bindings' => $this->bindings ?? null,
        ]);
    }
}
