<?php

use Look\Workflows\Core\Runtime\ExecutionResult;
use Look\Workflows\Core\Runtime\ExecutionResults;
use Look\Workflows\Core\Support\FluentData;
use Look\Workflows\Core\Testing\Steps\Mock;
use Tests\Fixtures\StepWithoutType;

test('execute with passthrough returned execution results', function () {

    $results = ExecutionResults::make()->output(['hello' => 'world']);

    $returned = Mock::make()
        ->return($results)
        ->execute();

    expect($returned)->toEqual($results);

});

test('execute will wrap returned single execution result', function () {

    $result = ExecutionResult::output(['hello' => 'world']);
    $wrapped = ExecutionResults::make()->push($result);

    $returned = Mock::make()
        ->return($result)
        ->execute();

    expect($returned)->toEqual($wrapped);

});

test('execute will passthrough data when no result returned', function () {

    $data = ['hello' => 'world'];

    $wrapped = ExecutionResults::make()->output($data);

    $returned = Mock::make()
        ->execute($data);

    expect($returned)->toEqual($wrapped);

});

test('execute will passthrough data when true returned', function () {

    $data = ['hello' => 'world'];

    $wrapped = ExecutionResults::make()->output($data);

    $returned = Mock::make()
        ->return(true)
        ->execute($data);

    expect($returned)->toEqual($wrapped);

});

test('execute will cast returned array into results', function () {

    $data = ['hello' => 'world'];
    $wrapped = ExecutionResults::make()->output($data);

    $returned = Mock::make()
        ->return($data)
        ->execute();

    expect($returned)->toEqual($wrapped);

});

test('execute will cast returned fluent data into results', function () {

    $data = ['hello' => 'world'];
    $wrapped = ExecutionResults::make()->output($data);

    $returned = Mock::make()
        ->return(FluentData::make($data))
        ->execute();

    expect($returned)->toEqual($wrapped);

});

test('execute with exception returns an error result', function () {

    $message = 'an error occurred';

    $returned = Mock::make()
        ->throw(new Exception($message))
        ->execute();

    expect($returned)->toHaveCount(1);
    expect($returned[0]->port)->toEqual('error');
    expect($returned[0]->data['error'])->not->toBeEmpty();
    expect($returned[0]->data['error'])->toEqual(['message' => $message, 'class' => Exception::class]);

});

test('export returns empty array', function () {

    $step = new StepWithoutType;
    $data = $step->export();

    expect($data)->toBeEmpty();

});

test('import does nothing', function () {

    $step = new StepWithoutType;
    $step->import([]);

    expect($step)->not->toBeNull();

});

test('toArray without type uses class', function () {

    $step = new StepWithoutType;

    $data = $step->toArray();

    expect(isset($data['type']))->toBeFalse();
    expect($data['class'])->toEqual(StepWithoutType::class);

});
