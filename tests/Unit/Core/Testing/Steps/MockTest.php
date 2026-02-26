<?php

use Look\Workflows\Core\Testing\Steps\Mock;

test('it can be constructed', function () {

    $step = Mock::make();
    expect($step)->not->toBeNull();

});

test('it uses test namespace for type', function () {

    $step = Mock::make();
    expect($step->type())->toEqual('test:mock');

});

test('it will pass through input data', function () {

    $step = Mock::make();

    $data = ['input' => 'hello world'];

    $results = $step
        ->execute($data);

    expect($results)->toHaveCount(1);
    expect($results[0]->data->toArray())->toEqual($data);
    expect($results[0]->port)->toEqual('output');

});
