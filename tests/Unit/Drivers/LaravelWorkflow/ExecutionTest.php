<?php

use Look\Workflows\Core\Runtime\Execution;
use Look\Workflows\Core\Runtime\ExecutionManager;
use Look\Workflows\Core\Steps;
use Look\Workflows\Core\Testing;
use Look\Workflows\Core\Workflow;
use Look\Workflows\Drivers\LaravelWorkflow\LaravelWorkflowDriver;
use Look\Workflows\Drivers\LaravelWorkflow\StepWrapper;
use Workflow\Models\StoredWorkflow;
use Workflow\States\WorkflowCompletedStatus;
use Workflow\WorkflowStub;

beforeEach(function () {
    WorkflowStub::fake();
    WorkflowStub::mock(StepWrapper::class, function ($context, $call) {
        return $call->execute();
    });

    $this->workflow = Workflow::make()
        ->addSteps([
            Steps\Data::make(['inject' => 'hello world'])
                ->name('start'),
            Testing\Steps\Mock::make(deterministic: false)
                ->name('driver'),
            Steps\Conditional::make([
                'first' => 'inject == "hello world"',
                'second' => false, 'third' => true,
            ])
                ->name('branch'),
            Testing\Steps\Mock::make()
                ->name('end1'),
            Testing\Steps\Mock::make()
                ->name('end2'),
            Testing\Steps\Mock::make()
                ->error('an error occured')
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

test('it can execute a workflow', function () {

    $execution = Execution::make($this->workflow)
        ->signal('start');

    ExecutionManager::make()
        ->register('default', LaravelWorkflowDriver::class)
        ->queue($execution);

    $stored = StoredWorkflow::first();

    expect($stored->status)->toBeInstanceOf(WorkflowCompletedStatus::class);

})
    ->skip(
        !class_exists(WorkflowStub::class),
        'laravel-workflow/laravel-workflow not installed'
    );
