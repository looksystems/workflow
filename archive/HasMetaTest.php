<?php

use Look\Workflows\Core\Workflow;

test('it can set/get a single meta value', function () {

    $workflow = new Workflow;

    $workflow->meta('test', true);
    expect($workflow->meta('test'))->toEqual(true);
    expect($workflow->hasMeta('test'))->toEqual(true);

    $workflow->setMeta('test', false);
    expect($workflow->getMeta('test'))->toEqual(false);
    expect($workflow->hasMeta('test'))->toEqual(true);

});

test('it can drop a single meta value', function () {

    $workflow = new Workflow;

    $workflow->meta('test', true);

    $workflow->dropMeta('test');
    expect($workflow->getMeta('test'))->toBeNull();
    expect($workflow->getMeta())->toEqual([]);
    expect($workflow->hasMeta())->toBeFalse();

});

test('it can set/get an array of meta values', function () {

    $workflow = new Workflow;

    $workflow->setMeta(['test' => true]);
    expect($workflow->getMeta())->toEqual(['test' => true]);

});

test('it can drop an array of meta values', function () {

    $workflow = new Workflow;
    $workflow->setMeta(['test' => true, 'another' => false]);

    $workflow->dropMeta(['test']);

    expect($workflow->getMeta('test'))->toBeNull();
    expect($workflow->getMeta())->toEqual(['another' => false]);
    expect($workflow->hasMeta())->toBeTrue();

});

test('it can drop all meta values', function () {

    $workflow = new Workflow;
    $workflow->setMeta(['test' => true]);

    $workflow->dropMeta();

    expect($workflow->getMeta('test'))->toBeNull();
    expect($workflow->getMeta())->toEqual([]);
    expect($workflow->hasMeta())->toBeFalse();
});
