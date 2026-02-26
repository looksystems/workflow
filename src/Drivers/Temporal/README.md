# Temporal Driver for Look Workflows

This driver integrates [Temporal](https://temporal.io/) with the Look Workflows system, allowing you to execute workflows using Temporal's distributed orchestration engine.

## Installation

The Temporal SDK is already installed as part of this package. You'll need:

1. **PHP Extensions**:
   - `ext-grpc` (required for client)
   - `ext-protobuf` (recommended for performance)

2. **RoadRunner** (required for workers):
   ```bash
   ./vendor/bin/rr get
   ```

3. **Temporal Server** (for local development):
   ```bash
   temporal server start-dev --log-level error
   ```

## Usage

### Basic Setup

```php
use Look\Workflows\Drivers\Temporal\TemporalDriver;
use Look\Workflows\Drivers\Temporal\TemporalConfig;
use Look\Workflows\Core\Runtime\ExecutionManager;

// Simple usage with defaults
$driver = new TemporalDriver();

// Or with configuration
$config = TemporalConfig::create()
    ->withAddress('localhost:7233')
    ->withTaskQueue('workflows')
    ->withNamespace('default');

$driver = $config->createDriver();

// Register with ExecutionManager
ExecutionManager::make()
    ->register('temporal', $driver)
    ->queue($execution, 'temporal');
```

### Advanced Configuration

```php
use Temporal\Client\WorkflowOptions;
use Temporal\Client\ClientOptions;

$workflowOptions = WorkflowOptions::new()
    ->withWorkflowExecutionTimeout('3600') // 1 hour
    ->withWorkflowRunTimeout('1800')       // 30 minutes
    ->withWorkflowTaskTimeout('30');       // 30 seconds

$clientOptions = ClientOptions::new()
    ->withServiceAddress('temporal.example.com:7233')
    ->withNamespace('production');

$config = TemporalConfig::create()
    ->withClientOptions($clientOptions)
    ->withDefaultWorkflowOptions($workflowOptions)
    ->withTaskQueue('production-workflows');

$driver = $config->createDriver();
```

### Running Workers

To execute workflows, you need to run Temporal workers:

```php
use Look\Workflows\Drivers\Temporal\TemporalWorkerFactory;

// Simple worker
TemporalWorkerFactory::createAndRun('workflows');

// Or with custom options
use Temporal\Worker\WorkerOptions;

$workerOptions = WorkerOptions::new()
    ->withMaxConcurrentActivityExecutionSize(100)
    ->withMaxConcurrentWorkflowExecutionSize(50);

$factory = TemporalWorkerFactory::create('workflows', $workerOptions);
$factory->run();
```

### Example Workflow Execution

```php
use Look\Workflows\Core\Steps;
use Look\Workflows\Core\Workflow;
use Look\Workflows\Core\Runtime\Execution;

// Create a workflow
$workflow = Workflow::make()
    ->addSteps([
        Steps\Data::make(['message' => 'Hello'])
            ->name('start'),
        Steps\Data::make(['message' => 'World'])
            ->name('end'),
    ])
    ->addLinks([
        'start' => 'end',
    ]);

// Execute with Temporal
$execution = Execution::make($workflow)
    ->signal('start', []);

ExecutionManager::make()
    ->register('temporal', $driver)
    ->queue($execution, 'temporal');
```

## Architecture

The Temporal driver consists of several components:

- **TemporalDriver**: Implements `ExecutionDriver` interface and queues executions
- **ExecutionWorkflow**: Temporal workflow that orchestrates step execution
- **StepActivity**: Temporal activity that executes individual workflow steps
- **TemporalConfig**: Configuration helper for easy setup
- **TemporalWorkerFactory**: Helper for creating and running workers

## Features

- **Distributed Execution**: Leverage Temporal's distributed architecture
- **Fault Tolerance**: Automatic retries and error handling
- **Scalability**: Scale workers independently
- **Observability**: Built-in workflow monitoring and debugging
- **Deterministic Replay**: Temporal ensures deterministic workflow execution
- **Activity Retries**: Configurable retry policies for individual steps

## Development

### Running Tests

```bash
./vendor/bin/pest tests/Unit/Drivers/Temporal/
```

### Starting Temporal Server

For local development:

```bash
temporal server start-dev --log-level error
```

### Worker Process

Create a worker script (e.g., `worker.php`):

```php
<?php

require_once 'vendor/autoload.php';

use Look\Workflows\Drivers\Temporal\TemporalWorkerFactory;

TemporalWorkerFactory::createAndRun('default');
```

Run it with:

```bash
php worker.php
```

## Configuration Options

### TemporalConfig Methods

- `withAddress(string $address)`: Set Temporal server address
- `withNamespace(string $namespace)`: Set Temporal namespace
- `withTaskQueue(string $taskQueue)`: Set task queue name
- `withDefaultWorkflowOptions(WorkflowOptions $options)`: Set default workflow options
- `withClientOptions(ClientOptions $options)`: Set client connection options

### Environment Variables

You can also configure using environment variables:

```bash
TEMPORAL_ADDRESS=localhost:7233
TEMPORAL_NAMESPACE=default
TEMPORAL_TASK_QUEUE=workflows
```

## Troubleshooting

### Common Issues

1. **gRPC Extension Missing**: Install `ext-grpc` for the client
2. **RoadRunner Not Found**: Run `./vendor/bin/rr get` to download
3. **Connection Refused**: Ensure Temporal server is running
4. **Worker Not Processing**: Check task queue names match

### Debugging

Use Temporal's web UI at http://localhost:8233 (when running locally) to monitor workflow executions.

## Links

- [Temporal PHP SDK Documentation](https://docs.temporal.io/develop/php)
- [Temporal PHP SDK Examples](https://github.com/temporalio/samples-php)
- [Temporal Server Setup](https://docs.temporal.io/cli#install)
