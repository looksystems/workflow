<?php

use Look\Workflows\Core\Exceptions\InvalidDirection;
use Look\Workflows\Core\Exceptions\StepNotFound;
use Look\Workflows\Core\Support\Port;
use Look\Workflows\Core\Support\PortType;
use Look\Workflows\Core\Support\Uuid;
use Look\Workflows\Core\Workflow;

test('port can be constructed from string', function () {

    $uuid = Uuid::generate();

    $port = Port::make($uuid, PortType::Output);
    expect($port->type)->toEqual(PortType::Output);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('output');

    $port = Port::make($uuid.':output');
    expect($port->type)->toEqual(PortType::Output);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('output');

    $port = Port::make($uuid.':output:custom');
    expect($port->type)->toEqual(PortType::Output);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('custom');

    $port = Port::make($uuid, PortType::Input);
    expect($port->type)->toEqual(PortType::Input);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('input');

    $port = Port::make($uuid.':input');
    expect($port->type)->toEqual(PortType::Input);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('input');

    $port = Port::input($uuid.':input:custom');
    expect($port->type)->toEqual(PortType::Input);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('custom');

});

test('input port can be constructed from string', function () {

    $uuid = Uuid::generate();
    $port = Port::input($uuid);

    expect($port->type)->toEqual(PortType::Input);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('input');

    $port = Port::input($uuid, 'custom');

    expect($port->type)->toEqual(PortType::Input);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('custom');

    $port = Port::input($uuid.':custom');

    expect($port->type)->toEqual(PortType::Input);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('custom');

});

test('input port can be constructed from array', function () {

    $uuid = Uuid::generate();
    $port = Port::input(['step' => $uuid]);

    expect($port->type)->toEqual(PortType::Input);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('input');

    $port = Port::input(['step' => $uuid, 'port' => 'custom']);

    expect($port->type)->toEqual(PortType::Input);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('custom');

});

test('input port does not throw exception when step in workflow', function () {

    $uuid = Uuid::generate();
    $workflow = Workflow::make()
        ->addStep(['uuid' => $uuid]);

    $port = Port::input($uuid, workflow: $workflow);

    expect($port)->not->toBeNull();

});

test('input port throws exception when step not in workflow', function () {

    $workflow = new Workflow;
    $uuid = Uuid::generate();

    $port = Port::input($uuid, workflow: $workflow);

})->throws(StepNotFound::class);

test('output port can be constructed from string', function () {

    $uuid = Uuid::generate();
    $port = Port::output($uuid);

    expect($port->type)->toEqual(PortType::Output);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('output');

    $port = Port::output($uuid, 'custom');

    expect($port->type)->toEqual(PortType::Output);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('custom');

    $port = Port::output($uuid.':custom');

    expect($port->type)->toEqual(PortType::Output);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('custom');

});

test('output port can be constructed from array', function () {

    $uuid = Uuid::generate();
    $port = Port::output(['step' => $uuid]);

    expect($port->type)->toEqual(PortType::Output);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('output');

    $port = Port::output(['step' => $uuid, 'port' => 'custom']);

    expect($port->type)->toEqual(PortType::Output);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('custom');

});

test('output port does not throw exception when step in workflow', function () {

    $uuid = Uuid::generate();
    $workflow = Workflow::make()
        ->addStep(['uuid' => $uuid]);

    $port = Port::output($uuid, workflow: $workflow);

    expect($port)->not->toBeNull();

});

test('output port throws exception when step not in workflow', function () {

    $workflow = new Workflow;
    $uuid = Uuid::generate();

    $port = Port::output($uuid, workflow: $workflow);

})->throws(StepNotFound::class);

test('error port can be constructed from string', function () {

    $uuid = Uuid::generate();
    $port = Port::error($uuid);

    expect($port->type)->toEqual(PortType::Output);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('error');

    $port = Port::error($uuid, 'custom');

    expect($port->type)->toEqual(PortType::Output);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('custom');

});

test('error port can be constructed from array', function () {

    $uuid = Uuid::generate();
    $port = Port::error(['step' => $uuid]);

    expect($port->type)->toEqual(PortType::Output);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('error');

    $port = Port::error(['step' => $uuid, 'port' => 'custom']);

    expect($port->type)->toEqual(PortType::Output);
    expect($port->step)->toEqual($uuid);
    expect($port->name)->toEqual('custom');

});

test('error port does not throw exception when step in workflow', function () {

    $uuid = Uuid::generate();
    $workflow = Workflow::make()
        ->addStep(['uuid' => $uuid]);

    $port = Port::error($uuid, workflow: $workflow);

    expect($port)->not->toBeNull();

});

test('error port throws exception when step not in workflow', function () {

    $workflow = new Workflow;
    $uuid = Uuid::generate();

    $port = Port::error($uuid, workflow: $workflow);

})->throws(StepNotFound::class);

test('toWorkflow throws exception when step not in workflow', function () {

    $workflow = new Workflow;
    $uuid = Uuid::generate();

    $port = Port::output($uuid)->toWorkflow($workflow);

})->throws(StepNotFound::class);

test('can test port direction', function () {

    $uuid = Uuid::generate();

    $port = Port::input($uuid);
    expect($port->isOutbound())->toBeFalse();
    expect($port->isInbound())->toBeTrue();

    $port = Port::output($uuid);
    expect($port->isOutbound())->toBeTrue();
    expect($port->isInbound())->toBeFalse();

    $port = Port::error($uuid);
    expect($port->isOutbound())->toBeTrue();
    expect($port->isInbound())->toBeFalse();

});

test('assertOutbound throws exception when not true', function () {

    $uuid = Uuid::generate();

    $port = Port::input($uuid);
    $port->assertInbound();
    expect(true)->toBeTrue();

    $port->assertOutbound();

})->throws(InvalidDirection::class);

test('assertInbound throws exception when not true ', function () {

    $uuid = Uuid::generate();

    $port = Port::output($uuid);
    $port->assertOutbound();
    expect(true)->toBeTrue();

    $port->assertInbound();

})->throws(InvalidDirection::class);
