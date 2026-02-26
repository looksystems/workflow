<?php

use Look\Workflows\Core\Steps\Loop;

test('it can iterate over input data', function () {

    $data = [
        1 => 'one', 2 => 'two', 'key3' => 'three',
    ];

    $loop = new Loop;

    $results = $loop->execute($data);

    expect($results)->toHaveCount(3);
    expect($results[0]->data->toArray())->toEqual(['item' => 'one', 'key' => 1]);
    expect($results[1]->data->toArray())->toEqual(['item' => 'two', 'key' => 2]);
    expect($results[2]->data->toArray())->toEqual(['item' => 'three', 'key' => 'key3']);

});

test('it can conditionally iterate over input data', function () {

    $data = [
        1 => 'one', 2 => 'two', 'key3' => 'three',
    ];

    $loop = Loop::make()
        ->condition('item == "two"');

    $results = $loop->execute($data);

    expect($results)->toHaveCount(1);
    expect($results[0]->data->toArray())->toEqual(['item' => 'two', 'key' => 2]);

});

test('it can send skipped items to a different port', function () {

    $data = [
        1 => 'one', 2 => 'two', 'key3' => 'three',
    ];

    $loop = Loop::make()
        ->condition('item == "two"')
        ->skip('skipped');

    $results = $loop->execute($data);

    expect($results)->toHaveCount(3);
    expect($results[0]->data->toArray())->toEqual(['item' => 'one', 'key' => 1]);
    expect($results[0]->port)->toEqual('skipped');
    expect($results[1]->data->toArray())->toEqual(['item' => 'two', 'key' => 2]);
    expect($results[1]->port)->toEqual('output');
    expect($results[2]->data->toArray())->toEqual(['item' => 'three', 'key' => 'key3']);
    expect($results[2]->port)->toEqual('skipped');

});

test('it can conditionally direct items to different ports', function () {

    $data = [
        1 => 'one', 2 => 'two', 'key3' => 'three',
    ];

    $loop = Loop::make()
        ->condition('item == "two" ? "output2" : "output"');

    $results = $loop->execute($data);

    expect($results)->toHaveCount(3);
    expect($results[0]->data->toArray())->toEqual(['item' => 'one', 'key' => 1]);
    expect($results[0]->port)->toEqual('output');
    expect($results[1]->data->toArray())->toEqual(['item' => 'two', 'key' => 2]);
    expect($results[1]->port)->toEqual('output2');
    expect($results[2]->data->toArray())->toEqual(['item' => 'three', 'key' => 'key3']);
    expect($results[2]->port)->toEqual('output');

});

test('will loop over scalar targets', function () {

    $data = [
        'target' => 'one',
    ];

    $loop = Loop::make()
        ->path('target');

    $results = $loop->execute($data);

    expect($results)->toHaveCount(1);
    expect($results[0]->data->toArray())->toEqual(['item' => 'one', 'key' => 0]);
    expect($results[0]->port)->toEqual('output');

});

test('it can iterate over as subset of input data', function () {

    $data = [
        'target' => [
            1 => 'one', 2 => 'two', 'key3' => 'three',
        ],
    ];

    $loop = Loop::make()
        ->path('target');

    $results = $loop->execute($data);

    expect($results)->toHaveCount(3);
    expect($results[0]->data->toArray())->toEqual(['item' => 'one', 'key' => 1]);
    expect($results[1]->data->toArray())->toEqual(['item' => 'two', 'key' => 2]);
    expect($results[2]->data->toArray())->toEqual(['item' => 'three', 'key' => 'key3']);

});
