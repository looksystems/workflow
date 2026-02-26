<?php

use Look\Workflows\Core\Steps\Process;

test('it can be constructed', function () {

    $step = new Process;
    expect($step)->not->toBeNull();

});

test('it can run process a command', function () {

    $step = Process::make()
        ->command('echo', ['hello world']);

    $results = $step->execute([]);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');
    expect($results[0]->data->get('output'))->toEqual("hello world\n");

});

test('it can run python', function () {

    $step = Process::make()
        ->command('python3', ['test.py'], json: true)
        ->root($this->resources('python'));

    $results = $step->execute([
        'name' => 'world',
    ]);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');
    expect($results[0]->data->get('message'))->toEqual('hello world');

});

test('it can run node', function () {

    $step = Process::make()
        ->command('node', ['test.js'], json: true)
        ->root($this->resources('node'));

    $results = $step->execute([
        'name' => 'world',
    ]);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');
    expect($results[0]->data->get('message'))->toEqual('hello world');

});
