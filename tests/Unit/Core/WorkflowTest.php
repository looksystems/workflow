<?php

use Look\Workflows\Core\Schemas\SchemaRegistry;
use Look\Workflows\Core\Support\Uuid;
use Look\Workflows\Core\Testing\Steps\Mock;
use Look\Workflows\Core\Workflow;

test('workflow can be constructed', function () {

    $workflow = new Workflow;

    expect($workflow)
        ->not->toBeNull();

});

test('can make workflow with static constructor', function () {

    $workflow = Workflow::make();

    expect($workflow)->not->toBeNull();

});

test('can make workflow with steps', function () {

    $workflow = Workflow::make([
        Mock::make(),
    ]);

    expect($workflow->hasSteps())
        ->toBeTrue();

});

test('can make workflow with custon schema registry', function () {

    $registry = new SchemaRegistry;

    $workflow = Workflow::make(registry: $registry);

    expect($workflow->schemas())
        ->toEqual($registry);

});

test('new workflow will self-assign uuid', function () {

    $workflow = new Workflow;

    expect($workflow->uuid())
        ->not->toBeEmpty();

});

test('can set workflow uuid', function () {

    $workflow = new Workflow;

    $uuid1 = Uuid::generate();

    $workflow->setUuid($uuid1);

    expect($workflow->getUuid())
        ->toEqual($uuid1);

    $uuid2 = Uuid::generate();

    $workflow->uuid($uuid2);

    expect($workflow->uuid())
        ->toEqual($uuid2);
});

test('new workflow has version set to zero', function () {

    $workflow = new Workflow;

    expect($workflow->version())
        ->toEqual(0);

});

test('can set workflow version', function () {

    $workflow = new Workflow;

    $workflow->setVersion(1);

    expect($workflow->getVersion())
        ->toEqual(1);

    $workflow->version(2);

    expect($workflow->version())
        ->toEqual(2);

});

test('can set workflow name', function () {

    $workflow = new Workflow;

    $workflow->setName('name');

    expect($workflow->getName())
        ->toEqual('name');

    $workflow->name('another name');

    expect($workflow->name())
        ->toEqual('another name');

});

test('can set workflow description', function () {

    $workflow = new Workflow;

    $workflow->setDescription('description');

    expect($workflow->getDescription())
        ->toEqual('description');

    $workflow->description('another description');

    expect($workflow->description())
        ->toEqual('another description');

});

test('workflow can import from array', function () {

    $workflow = Workflow::make()
        ->fromArray([
            'name' => 'name',
            'description' => 'description',
            'version' => 1,
            'steps' => [
                'step1' => ['type' => 'noop'],
                'step2' => ['type' => 'noop'],
            ],
            'links' => [
                'step1' => 'step2',
            ],
        ]);

    $array = $workflow->toArray();

    expect($array['uuid'])
        ->toEqual($workflow->uuid());

    expect($array['name'])
        ->toEqual('name');

    expect($array['description'])
        ->toEqual('description');

    expect($array['version'])
        ->toEqual(1);

    expect($workflow->hasStep('step1'))
        ->toBeTrue();

    expect($workflow->hasStep('step2'))
        ->toBeTrue();

    expect($workflow->hasLink('step1', 'step2'))
        ->toBeTrue();

});

test('empty workflow can export to array', function () {

    $workflow = Workflow::make();

    $array = $workflow->toArray();

    expect($array)
        ->toEqual([
            'uuid' => $workflow->uuid(),
            'version' => 0,
        ]);

});

test('fully loaded workflow can export to array', function () {

    $step1 = Mock::make();
    $step2 = Mock::make();

    $workflow = Workflow::make([
        'step1' => $step1,
        'step2' => $step2,
    ])
        ->links([
            'step1' => 'step2',
        ])
        ->name('name')
        ->description('description');

    $array = $workflow->toArray();

    expect($array)
        ->toEqual([
            'uuid' => $workflow->uuid(),
            'name' => 'name',
            'description' => 'description',
            'version' => 0,
            'steps' => [
                $step1->uuid() => $step1->toArray(),
                $step2->uuid() => $step2->toArray(),
            ],
            'links' => [
                $step1->uuid().':output' => [
                    $step2->uuid().':input',
                ],
            ],
        ]);

});

test('workflow can serialize and unserialize', function () {

    $workflow = Workflow::make([
        'step1' => Mock::make(),
        'step2' => Mock::make(),
    ])
        ->links([
            'step1' => 'step2',
        ])
        ->name('name')
        ->description('description');

    $serialized = serialize($workflow);

    $unserialized = unserialize($serialized);

    expect($workflow->toArray())
        ->toEqual($unserialized->toArray());

});
