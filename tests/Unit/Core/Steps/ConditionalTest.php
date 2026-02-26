<?php

use Look\Workflows\Core\Steps\Conditional;

test('it supports default output port', function () {

    $data = [
        1 => 'one', 2 => 'two', 'key3' => 'three',
    ];

    $loop = Conditional::make()
        ->default('custom');

    $results = $loop->execute($data);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('custom');
    expect($results[0]->data->toArray())->toEqual($data);

});
