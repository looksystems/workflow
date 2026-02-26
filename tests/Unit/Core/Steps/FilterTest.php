<?php

use Look\Workflows\Core\Steps\Filter;

test('it can filter input data', function () {

    $data = [
        1 => 'one', 2 => 'two', 'key3' => 'three',
    ];

    $step = Filter::make()
        ->filter('item == "two"');

    $results = $step->execute($data);

    expect($results)->toHaveCount(1);
    expect($results[0]->data->toArray())->toEqual([2 => 'two']);

});

test('it can filter a subset of input data', function () {

    $data = [
        'target' => [
            1 => 'one', 2 => 'two', 'key3' => 'three',
        ],
        'preserved' => true,
    ];

    $step = Filter::make()
        ->filter('item == "two"', 'target');

    $results = $step->execute($data);

    expect($results)->toHaveCount(1);
    expect($results[0]->data->toArray())->toEqual(['target' => [2 => 'two'], 'preserved' => true]);

});

test('input data is not filtered if no condition provided', function () {

    $data = [
        1 => 'one', 2 => 'two', 'key3' => 'three',
    ];

    $step = new Filter;

    $results = $step->execute($data);

    expect($results)->toHaveCount(1);
    expect($results[0]->data->toArray())->toEqual($data);

});

test('scalar target is removed filtered', function () {

    $data = [
        1 => 'one', 2 => 'two', 'key3' => 'three',
    ];

    $step = Filter::make()
        ->filter('item == "two"', 'key3');

    $results = $step->execute($data);

    expect($results)->toHaveCount(1);
    expect($results[0]->data->toArray())->toEqual([1 => 'one', 2 => 'two']);

});

test('scalar target is kept when matched', function () {

    $data = [
        1 => 'one', 2 => 'two', 'key3' => 'three',
    ];

    $step = Filter::make()
        ->filter('item == "three"', 'key3');

    $results = $step->execute($data);

    expect($results)->toHaveCount(1);
    expect($results[0]->data->toArray())->toEqual($data);

});
