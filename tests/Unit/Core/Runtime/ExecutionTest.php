<?php

use Look\Workflows\Core\Runtime\Execution;
use Look\Workflows\Core\Runtime\ExecutionManager;
use Look\Workflows\Core\Steps;
use Look\Workflows\Core\Testing;
use Look\Workflows\Core\Workflow;

beforeEach(function () {
    $this->workflow = Workflow::make()
        ->addSteps([
            Steps\Data::make(['inject' => 'hello world'])
                ->name('start')
                ->link('driver'),
            Testing\Steps\Mock::make(deterministic: false)
                ->name('driver')
                ->link('branch'),
            Steps\Conditional::make([
                'first' => 'inject == "hello world"',
                'second' => false, 'third' => true,
            ])
                ->name('branch')
                ->links([
                    'first' => 'end1',
                    'second' => 'end2',
                    'third' => 'error2',
                ]),
            Testing\Steps\Mock::make()
                ->name('end1')
                ->link('error1'),
            Testing\Steps\Mock::make()
                ->name('end2'),
            Testing\Steps\Mock::make()
                ->error('an error occured')
                ->name('error1'),
            Testing\Steps\Mock::make()
                ->error(new Exception('custom exception'))
                ->name('error2'),
        ]);
});

test('it can be serialized and unserialized', function () {

    $execution = Execution::make($this->workflow)
        ->signal('start', []);

    expect($execution->queue()->count())->toEqual(1);
    expect($execution->stack())->toHaveCount(0);

    $encoded = serialize($execution);
    $unserialized = unserialize($encoded);
    expect($unserialized->toArray())
        ->toEqual($execution->toArray());

    expect($unserialized->queue()->count())->toEqual(1);
    expect($unserialized->stack())->toHaveCount(0);

    ExecutionManager::make()
        ->queue($unserialized);

    expect($unserialized->queue()->count())->toEqual(0);
    $stack = $unserialized->stack();

    expect($stack)->toHaveCount(6);

    expect($stack[3]['output'][0]->data->toArray())->toEqual(['error' => ['message' => 'custom exception', 'class' => Exception::class]]);

    expect($stack[4]['output'][0]->data->toArray())->toEqual(['inject' => 'hello world']);

    expect($stack[5]['output'][0]->data->toArray())->toEqual(['error' => ['message' => 'an error occured']]);

});

test('it can execute a workflow', function () {

    $execution = Execution::make($this->workflow)
        ->signal('start', []);

    ExecutionManager::make()
        ->queue($execution);

    $stack = $execution->stack();

    expect($stack)->toHaveCount(6);

    expect($stack[3]['input']->step()->name())->toEqual('error2');
    expect($stack[3]['output'][0]->data->toArray())->toEqual(['error' => ['message' => 'custom exception', 'class' => Exception::class]]);

    expect($stack[4]['input']->step()->name())->toEqual('end1');
    expect($stack[4]['output'][0]->data->toArray())->toEqual(['inject' => 'hello world']);

    expect($stack[5]['input']->step()->name())->toEqual('error1');
    expect($stack[5]['output'][0]->data->toArray())->toEqual(['error' => ['message' => 'an error occured']]);

});

test('it will not execute an already running workflow', function () {

    $called = 0;
    $running = null;
    $response = null;
    $workflow = new Workflow;
    $execution = Execution::make($workflow);
    $workflow->addStep(
        Steps\Lambda::make(
            function ($data) use ($execution, &$response, &$running, &$called) {
                // trying to run already running execution should return invalid generator
                $running = $execution->isRunning();
                $response = $execution->run();
                $called++;
            }
        )
            ->name('start')
    );

    $execution->signal('start', []);

    ExecutionManager::make()
        ->queue($execution);

    expect($running)->toBeTrue();
    expect($response->valid())->toBeFalse();
    expect($called)->toEqual(1);
});

test('it can be serialized and unserialized with results', function () {

    $execution = Execution::make($this->workflow)
        ->signal('start', []);

    ExecutionManager::make()
        ->queue($execution);

    $stack = $execution->stack();

    $encoded = serialize($execution);
    $unserialized = unserialize($encoded);

    expect(json_encode($unserialized))
        ->toEqual(json_encode($execution));

    $stack = $unserialized->stack();

    expect($stack)->toHaveCount(6);

    expect($stack[3]['input']->step()->name())->toEqual('error2');
    expect($stack[3]['output'][0]->data->toArray())->toEqual(['error' => ['message' => 'custom exception', 'class' => Exception::class]]);

    expect($stack[4]['input']->step()->name())->toEqual('end1');
    expect($stack[4]['output'][0]->data->toArray())->toEqual(['inject' => 'hello world']);

    expect($stack[5]['input']->step()->name())->toEqual('error1');
    expect($stack[5]['output'][0]->data->toArray())->toEqual(['error' => ['message' => 'an error occured']]);

});
