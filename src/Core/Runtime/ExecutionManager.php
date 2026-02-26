<?php

namespace Look\Workflows\Core\Runtime;

use Closure;
use Look\Workflows\Core\Contracts\ExecutionDriver;
use Look\Workflows\Core\Drivers\SyncDriver;
use Look\Workflows\Core\Exceptions\DriverException;
use Look\Workflows\Core\Workflow;

// use Illuminate\Support\Facades\App;

class ExecutionManager
{
    protected array $drivers = [];
    protected array $registered = [];

    public static function make(): self
    {
        return new self;
    }

    // EXECUTION

    /**
     * @throws DriverException
     */
    public function queue(Workflow|Execution $executionOrWorkflow, ExecutionDriver|string $driver = 'default'): void
    {
        if (is_string($driver)) {
            $driver = $this->driver($driver);
        }

        if ($executionOrWorkflow instanceof Workflow) {
            $executionOrWorkflow = Execution::make($executionOrWorkflow);
        }

        $driver->queue($executionOrWorkflow);
    }

    // DRIVERS

    /**
     * @throws DriverException
     */
    public function driver(string $name = 'default'): ExecutionDriver
    {
        if (!isset($this->drivers[$name])) {
            $driver = $this->resolveDriver($name);
            if (!$driver) {
                throw new DriverException("Invalid '$name' workflow driver");
            }

            $this->drivers[$name] = $driver;
        }

        return $this->drivers[$name];
    }

    public function register(string $name, ExecutionDriver|Closure|string $driver): self
    {
        if ($driver instanceof ExecutionDriver) {
            $this->drivers[$name] = $driver;
            unset($this->registered[$name]);
        } else {
            $this->registered[$name] = $driver;
        }

        return $this;
    }

    protected function resolveDriver(string $name): ?ExecutionDriver
    {
        if (isset($this->registered[$name])) {
            $driver = $this->registered[$name];
            if (is_string($driver)) {
                return new $driver; // App::make($driver);
            }

            return $driver();
        }

        return ($name === 'default') ? new SyncDriver : null;
    }
}
