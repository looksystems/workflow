<?php

use Look\Workflows\Core\Steps\Data;

test('it will output array data', function () {

    $data = ['input' => 'hello world'];

    $step = Data::make(data: $data);

    $results = $step->execute($data);

    expect($results)->toHaveCount(1);
    expect($results[0]->data->toArray())->toEqual($data);
    expect($results[0]->port)->toEqual('output');

});

test('it will output json data', function () {

    $data = ['input' => 'hello world'];

    $step = Data::make(data: json_encode($data));

    $results = $step->execute($data);

    expect($results)->toHaveCount(1);
    expect($results[0]->data->toArray())->toEqual($data);
    expect($results[0]->port)->toEqual('output');

});
