<?php

use Look\Workflows\Laravel\Steps\Http;
use Illuminate\Support\Facades\Http as HttpClient;

test('it can be constructed', function () {

    $step = new Http;
    expect($step)->not->toBeNull();

});

test('it can make a GET call', function () {

    $expect = [
        'one' => 'two',
        'key' => 'value',
    ];

    if (getenv('HTTP_CLIENT') === 'mock') {
        HttpClient::fake(['*' => HttpClient::response(json_encode($expect), 200, ['Content-Type' => 'application/json'])]);
    }

    $step = Http::make([
        'url' => 'http://echo.jsontest.com/key/value/one/two',
    ]);

    $results = $step->execute([]);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');

    $data = $results[0]->data->toArray();

    expect(array_keys($data))->toEqual(['status', 'reason', 'headers', 'cookies', 'body', 'data']);

    expect($data['status'])->toEqual(200);
    expect($data['reason'])->toEqual('OK');
    expect($data['headers'])->not->toBeEmpty();
    expect($data['cookies'])->toEqual([]);
    expect(json_decode($data['body']))->toEqual((object) $expect);
    expect($data['data'])->toEqual($expect);

});

test('it can make a POST call', function () {

    $expect = [
        'size' => 1,
        'parse_time_nanoseconds' => 18254,
        'object_or_array' => 'object',
        'validate' => true,
        'empty' => false,
    ];

    if (getenv('HTTP_CLIENT') === 'mock') {
        HttpClient::fake(['*' => HttpClient::response(json_encode($expect), 200, ['Content-Type' => 'application/json'])]);
    }

    $step = Http::make([
        'method' => 'POST',
        'url' => 'http://validate.jsontest.com/?json=%7B%22key%22:%22value%22%7D',
    ]);

    $results = $step->execute([]);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');

    $data = $results[0]->data->toArray();

    expect(array_keys($data))->toEqual(['status', 'reason', 'headers', 'cookies', 'body', 'data']);

    expect($data['status'])->toEqual(200);
    expect($data['reason'])->toEqual('OK');
    expect($data['headers'])->not->toBeEmpty();
    expect($data['cookies'])->toEqual([]);
    expect($data['data']['size'])->toEqual(1);
    expect($data['data']['object_or_array'])->toEqual('object');
    expect($data['data']['empty'])->toEqual(false);

});
