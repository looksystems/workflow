<?php

use Look\Workflows\Core\Exceptions\StepNotFound;
use Look\Workflows\Core\Support\Uuid;
use Look\Workflows\Core\Testing\Steps\Mock;
use Look\Workflows\Core\Workflow;

test('it can add a step', function () {

    $step = Mock::make();
    $workflow = Workflow::make();

    $workflow->addStep($step);

    expect($workflow->hasStep($step))->toBeTrue();
    expect($workflow->hasSteps())->toBeTrue();

});

test('it can add a step using short method', function () {

    $step = Mock::make();
    $workflow = Workflow::make();

    $workflow->step($step);

    expect($workflow->hasStep($step))->toBeTrue();
    expect($workflow->hasSteps())->toBeTrue();

});

test('it can add a named step', function () {

    $step = (Mock::make())->setName('step1');
    $workflow = Workflow::make();

    $workflow->addStep($step);

    expect($workflow->hasStep($step))->toBeTrue();
    expect($workflow->hasSteps())->toBeTrue();

    expect($workflow->hasStep('step1'))->toBeTrue();
    expect($workflow->hasSteps())->toBeTrue();

});

test('it can add a named step with links', function () {

    $step1 = (Mock::make())->setName('step1')
        ->link('step2');

    $step2 = (Mock::make())->setName('step2');

    $workflow = Workflow::make([$step1, $step2]);

    expect($workflow->getDestinations('step1'))
        ->not->toBeEmpty();

});

test('it can link a named step after registering with workflow', function () {

    $step1 = (Mock::make())->setName('step1');

    $step2 = (Mock::make())->setName('step2');

    $workflow = Workflow::make([$step1, $step2]);

    $step1->link('step2');

    expect($workflow->getDestinations('step1'))
        ->not->toBeEmpty();

});

test('it can add a step using an array', function () {

    $uuid = Uuid::generate();
    $workflow = Workflow::make();

    $workflow->addStep([
        'uuid' => $uuid,
        'type' => 'unknown',
    ]);

    expect($workflow->hasStep($uuid))->toBeTrue();
    expect($workflow->hasSteps())->toBeTrue();

});

test('it can add step using an array with links', function () {

    $workflow = Workflow::make();

    $workflow->addStep([
        'name' => 'one',
        'type' => 'unknown',
        'links' => [
            'one' => 'two',
        ],
    ]);

    $workflow->addStep([
        'name' => 'two',
        'type' => 'unknown',
    ]);

    expect($workflow->getDestinations('one'))
        ->not->toBeEmpty();

});

test('it can add a step as an array definition using short method', function () {

    $uuid = Uuid::generate();
    $workflow = Workflow::make();

    $workflow->step([
        'uuid' => $uuid,
        'type' => 'unknown',
    ]);

    expect($workflow->hasStep($uuid))->toBeTrue();
    expect($workflow->hasSteps())->toBeTrue();

});

test('it can add a named step as an array definition', function () {

    $workflow = Workflow::make();
    $workflow->addStep([
        'name' => 'step1',
        'type' => 'unknown',
    ]);

    expect($workflow->hasStep('step1'))->toBeTrue();
    expect($workflow->hasSteps())->toBeTrue();

});

test('it can add multiple steps', function () {

    $step1 = (Mock::make())->setName('step1');
    $step2 = (Mock::make())->setName('step2');

    $workflow = Workflow::make();

    $workflow->addSteps([
        $step1,
        $step2,
    ]);

    expect($workflow->hasSteps())->toBeTrue();
    expect($workflow->hasStep($step1))->toBeTrue();
    expect($workflow->hasStep($step2))->toBeTrue();
    expect($workflow->hasStep('step1'))->toBeTrue();
    expect($workflow->hasStep('step2'))->toBeTrue();

});

test('it can add multiple steps with short method', function () {

    $step1 = (Mock::make())->setName('step1');
    $step2 = (Mock::make())->setName('step2');

    $workflow = Workflow::make();

    $workflow->steps([
        $step1,
        $step2,
    ]);

    expect($workflow->hasSteps())->toBeTrue();
    expect($workflow->hasStep($step1))->toBeTrue();
    expect($workflow->hasStep($step2))->toBeTrue();
    expect($workflow->hasStep('step1'))->toBeTrue();
    expect($workflow->hasStep('step2'))->toBeTrue();

});

test('it can add multiple steps with names', function () {

    $workflow = Workflow::make();

    $workflow->addSteps([
        'step1' => ['type' => 'noop'],
        'step2' => ['type' => 'noop'],
    ]);

    expect($workflow->hasSteps())->toBeTrue();
    expect($workflow->hasStep('step1'))->toBeTrue();
    expect($workflow->hasStep('step2'))->toBeTrue();

});

test('it can remove a step', function () {

    $step = Mock::make();
    $workflow = Workflow::make();

    $workflow->addStep($step);

    expect($workflow->hasStep($step->uuid()))->toBeTrue();
    expect($workflow->hasSteps())->toBeTrue();

    $workflow->removeStep($step);

    expect($workflow->hasStep($step))->toBeFalse();
    expect($workflow->hasSteps())->toBeFalse();

});

test('it can remove a step by uuid', function () {

    $uuid = Uuid::generate();
    $workflow = Workflow::make();

    $workflow->addStep([
        'uuid' => $uuid,
        'type' => 'unknown',
    ]);

    expect($workflow->hasStep($uuid))->toBeTrue();
    expect($workflow->hasSteps())->toBeTrue();

    $workflow->removeStep($uuid);

    expect($workflow->hasStep($uuid))->toBeFalse();
    expect($workflow->hasSteps())->toBeFalse();

});

test('it can remove a named step by name', function () {

    $workflow = Workflow::make();

    $workflow->addStep([
        'name' => 'step1',
        'type' => 'unknown',
    ]);

    expect($workflow->hasStep('step1'))->toBeTrue();
    expect($workflow->hasSteps())->toBeTrue();

    $workflow->removeStep('step1');

    expect($workflow->hasStep('step1'))->toBeFalse();
    expect($workflow->hasSteps())->toBeFalse();

});

test('by default it throws am exception when removing an unknown step', function () {

    $workflow = Workflow::make();

    $workflow->removeStep('unknown', throwIfNotFound: false);
    expect(true)->toBeTrue();

    $workflow->removeStep('unknown');

})->throws(StepNotFound::class);

test('it can remove multiple steps', function () {

    $step1 = Mock::make();
    $step2 = Mock::make();
    $workflow = Workflow::make();

    $workflow->addSteps([$step1, $step2]);

    expect($workflow->hasSteps())->toBeTrue();

    $workflow->removeSteps([$step1, $step2]);

    expect($workflow->hasStep($step1))->toBeFalse();
    expect($workflow->hasStep($step2))->toBeFalse();
    expect($workflow->hasSteps())->toBeFalse();

});

test('it can remove all steps', function () {

    $workflow = Workflow::make();

    $workflow->addSteps([
        'step1' => ['type' => 'noop'],
        'step2' => ['type' => 'noop'],
    ]);

    expect($workflow->hasSteps())->toBeTrue();
    expect($workflow->hasStep('step1'))->toBeTrue();
    expect($workflow->hasStep('step2'))->toBeTrue();

    $workflow->removeAllSteps();

    expect($workflow->hasSteps())->toBeFalse();
    expect($workflow->hasStep('step1'))->toBeFalse();
    expect($workflow->hasStep('step2'))->toBeFalse();

});

test('it can get a step', function () {

    $step = Mock::make();
    $workflow = Workflow::make();
    $workflow->addStep($step);

    $found = $workflow->getStep($step->uuid());

    expect($found)->not->toBeNull();

});

test('it can get a step using short method', function () {

    $step = Mock::make();
    $workflow = Workflow::make();
    $workflow->addStep($step);

    $found = $workflow->step($step->uuid());

    expect($found)->not->toBeNull();

});

test('by default it throws am exception when getting an unknown step', function () {

    $workflow = Workflow::make();

    $found = $workflow->getStep('unknown', throwIfNotFound: false);
    expect($found)->toBeNull();

    $found = $workflow->getStep('unknown');

})->throws(StepNotFound::class);

test('it can get step uuid', function () {

    $step = (Mock::make())->setName('step1');
    $workflow = Workflow::make();
    $workflow->addStep($step);

    expect($workflow->getStepUuid($step))->toEqual($step->uuid());
    expect($workflow->getStepUuid($step->uuid()))->toEqual($step->uuid());
    expect($workflow->getStepUuid('step1'))->toEqual($step->uuid());

});
