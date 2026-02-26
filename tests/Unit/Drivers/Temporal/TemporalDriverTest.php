<?php

use Look\Workflows\Core\Runtime\Execution;
use Look\Workflows\Core\Runtime\ExecutionManager;
use Look\Workflows\Core\Steps;
use Look\Workflows\Core\Testing;
use Look\Workflows\Core\Workflow;
use Look\Workflows\Drivers\Temporal\TemporalDriver;
use Look\Workflows\Drivers\Temporal\TemporalConfig;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Client\WorkflowOptions;

test('temporal driver can be constructed', function () {
    $mockClient = Mockery::mock(WorkflowClientInterface::class);
    $driver = new TemporalDriver($mockClient);
    
    expect($driver)->toBeInstanceOf(TemporalDriver::class);
});

beforeEach(function () {
    $this->workflow = Workflow::make()
        ->addSteps([
            Steps\Data::make(['inject' => 'hello world'])
                ->name('start'),
            Testing\Steps\Mock::make(deterministic: false)
                ->name('driver'),
            Steps\Conditional::make([
                'first' => 'inject == "hello world"',
                'second' => false, 
                'third' => true,
            ])
                ->name('branch')
                ->links([
                    'first' => 'end1',
                    'second' => 'end2',
                    'third' => 'error2',
                ]),
            Testing\Steps\Mock::make()
                ->name('end1'),
            Testing\Steps\Mock::make()
                ->name('end2'),
            Testing\Steps\Mock::make()
                ->error('an error occurred')
                ->name('error1'),
            Testing\Steps\Mock::make()
                ->error(new Exception('custom exception'))
                ->name('error2'),
        ])
        ->addLinks([
            'start' => 'driver',
            'driver' => 'branch',
            'branch:first' => 'end1',
            'branch:second' => 'end2',
            'branch:third' => 'error2',
            'end1' => 'error1',
        ]);
});

test('it can create a temporal driver', function () {
    $mockClient = Mockery::mock(WorkflowClientInterface::class);
    $driver = new TemporalDriver($mockClient);
    
    expect($driver)->toBeInstanceOf(TemporalDriver::class);
});

test('it can create a temporal driver with config', function () {
    // Test the config creation itself without creating the actual client
    $config = TemporalConfig::create()
        ->withAddress('localhost:7233')
        ->withTaskQueue('test-queue')
        ->withNamespace('test-namespace');
    
    expect($config->getAddress())->toBe('localhost:7233')
        ->and($config->getTaskQueue())->toBe('test-queue')
        ->and($config->getNamespace())->toBe('test-namespace');
});

test('it can register temporal driver with execution manager', function () {
    $mockClient = Mockery::mock(WorkflowClientInterface::class);
    $driver = new TemporalDriver($mockClient);
    
    $manager = ExecutionManager::make()
        ->register('temporal', $driver);
    
    $retrievedDriver = $manager->driver('temporal');
    
    expect($retrievedDriver)->toBeInstanceOf(TemporalDriver::class);
});

test('it can queue an execution with temporal driver', function () {
    $execution = Execution::make($this->workflow)
        ->signal('start', []);
    
    // Create mock client and workflow stub
    $mockClient = Mockery::mock(WorkflowClientInterface::class);
    $mockWorkflowStub = Mockery::mock();
    
    // Setup expectations
    $mockClient->shouldReceive('newWorkflowStub')
        ->once()
        ->with(Mockery::type('string'), Mockery::type(WorkflowOptions::class))
        ->andReturn($mockWorkflowStub);
    
    $mockWorkflowStub->shouldReceive('execute')
        ->once()
        ->with($execution);
    
    $driver = new TemporalDriver($mockClient);
    $driver->queue($execution);
    
    expect($execution)->toBeInstanceOf(Execution::class);
    expect($driver)->toBeInstanceOf(TemporalDriver::class);
});

test('temporal config can be configured fluently', function () {
    $config = TemporalConfig::create()
        ->withAddress('temporal.example.com:7233')
        ->withNamespace('production')
        ->withTaskQueue('workflows');
    
    expect($config->getAddress())->toBe('temporal.example.com:7233');
    expect($config->getNamespace())->toBe('production');
    expect($config->getTaskQueue())->toBe('workflows');
});
