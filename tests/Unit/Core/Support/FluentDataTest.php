<?php

use Look\Workflows\Core\Support\FluentData;

test('it can be constructed', function () {

    $FluentData = new FluentData;
    expect($FluentData)->not->toBeNull();

});

test('it can query using jmes path', function () {

    $FluentData = FluentData::make([
        'one' => [
            'two' => [
                'three' => 3,
            ],
        ],
        'four' => 4,
    ]);

    $result = $FluentData->query('one.two.three');

    expect($result)->toEqual(3);

});

test('it can evaluate expressions', function () {

    $FluentData = FluentData::make([
        'one' => [
            'two' => [
                'three' => 3,
            ],
        ],
        'four' => 4,
    ]);

    $result = $FluentData->evaluate("one['two']['three'] + four");
    expect($result)->toEqual(7);

});

test('it can set FluentData', function () {

    $FluentData = FluentData::make()
        ->set('one.two.three', 3)
        ->set('four', 4);

    $result = $FluentData->evaluate("value('one.two.three') + four");
    expect($result)->toEqual(7);

});

test('it can fill FluentData', function () {

    $FluentData = FluentData::make([
        'one' => [
            'two' => [
                'three' => 3,
            ],
        ],
        'four' => 4,
    ]);

    // fyi: will not overwrite existing FluentData
    $FluentData->fill('one.two.three', 4);

    $result = $FluentData->evaluate("value('one.two.three') + four");
    expect($result)->toEqual(7);

});

test('it can forget FluentData', function () {

    $FluentData = FluentData::make([
        'one' => [
            'two' => [
                'three' => 3,
            ],
        ],
        'four' => 4,
    ]);

    $FluentData->forget('one.two.three');

    $result = $FluentData->get('one.two.three');
    expect($result)->toBeNull();

});
