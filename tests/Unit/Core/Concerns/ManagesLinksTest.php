<?php

use Look\Workflows\Core\Support\PortType;
use Look\Workflows\Core\Support\Uuid;
use Look\Workflows\Core\Workflow;

test('it can add a link', function () {

    $uuid1 = Uuid::generate();
    $uuid2 = Uuid::generate();

    $workflow = Workflow::make()
        ->addSteps([
            [
                'uuid' => $uuid1,
                'type' => 'unknown',
            ],
            [
                'uuid' => $uuid2,
                'type' => 'unknown',
            ],
        ]);

    $workflow->addLink($uuid1, $uuid2);

    expect($workflow->hasLink($uuid1, $uuid2))->toBeTrue();

    $destinations = $workflow->getDestinations($uuid1);

    expect($destinations)->toHaveCount(1);

    $first = current($destinations);

    expect($first->step)->toEqual($uuid2);
    expect($first->name)->toEqual('input');
    expect($first->type)->toEqual(PortType::Input);

    $sources = $workflow->getSources($uuid2);

    expect($sources)->toHaveCount(1);

    $first = current($sources);

    expect($first->step)->toEqual($uuid1);
    expect($first->name)->toEqual('output');
    expect($first->type)->toEqual(PortType::Output);

});

test('it can add a link using short methods', function () {

    $uuid1 = Uuid::generate();
    $uuid2 = Uuid::generate();

    $workflow = Workflow::make()
        ->addSteps([
            [
                'uuid' => $uuid1,
                'type' => 'unknown',
            ],
            [
                'uuid' => $uuid2,
                'type' => 'unknown',
            ],
        ]);

    $workflow->link($uuid1, $uuid2);

    expect($workflow->hasLink($uuid1, $uuid2))->toBeTrue();

});

test('it can remove a link', function () {

    $uuid1 = Uuid::generate();
    $uuid2 = Uuid::generate();

    $workflow = Workflow::make()
        ->addSteps([
            [
                'uuid' => $uuid1,
                'type' => 'unknown',
            ],
            [
                'uuid' => $uuid2,
                'type' => 'unknown',
            ],
        ])
        ->addLinks(
            [$uuid1 => $uuid2]
        );

    expect($workflow->hasLink($uuid1, $uuid2))->toBeTrue();

    $workflow->removeLink($uuid1, $uuid2);

    expect($workflow->hasLink($uuid1, $uuid2))->toBeFalse();

});

test('it can remove array of links', function () {

    $uuid1 = Uuid::generate();
    $uuid2 = Uuid::generate();
    $uuid3 = Uuid::generate();

    $workflow = Workflow::make()
        ->addSteps([
            [
                'uuid' => $uuid1,
                'type' => 'unknown',
            ],
            [
                'uuid' => $uuid2,
                'type' => 'unknown',
            ],
            [
                'uuid' => $uuid3,
                'type' => 'unknown',
            ],
        ])
        ->addLinks([
            $uuid1 => $uuid2,
            $uuid2 => $uuid3,
        ]);

    expect($workflow->hasLink($uuid1, $uuid2))->toBeTrue();
    expect($workflow->hasLink($uuid2, $uuid3))->toBeTrue();

    $workflow->removeLinks([$uuid1 => $uuid2]);

    expect($workflow->hasLink($uuid1, $uuid2))->toBeFalse();
    expect($workflow->hasLink($uuid2, $uuid3))->toBeTrue();

});

test('it can remove all links', function () {

    $uuid1 = Uuid::generate();
    $uuid2 = Uuid::generate();
    $uuid3 = Uuid::generate();

    $workflow = Workflow::make()
        ->addSteps([
            [
                'uuid' => $uuid1,
                'type' => 'unknown',
            ],
            [
                'uuid' => $uuid2,
                'type' => 'unknown',
            ],
            [
                'uuid' => $uuid3,
                'type' => 'unknown',
            ],
        ])
        ->addLinks([
            $uuid1 => $uuid2,
            $uuid2 => $uuid3,
        ]);

    expect($workflow->hasLink($uuid1, $uuid2))->toBeTrue();
    expect($workflow->hasLink($uuid2, $uuid3))->toBeTrue();

    $workflow->removeAllLinks();

    expect($workflow->hasLink($uuid1, $uuid2))->toBeFalse();
    expect($workflow->hasLink($uuid2, $uuid3))->toBeFalse();

});
