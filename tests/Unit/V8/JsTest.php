<?php

use Look\Workflows\V8\Js;

test('it can be constructed', function () {

    $step = new Js;
    expect($step)->not->toBeNull();

})
    ->skip(
        !extension_loaded('v8js'),
        'v8js extension not installed'
    )
    ->skip(
        !class_exists(\Chenos\V8JsModuleLoader\ModuleLoader::class),
        'chenos/v8js-module-loader not installed'
    );

test('it can execute a query', function () {

    $step = Js::make()
        ->script('PHP.output({hello: "world"});');

    $results = $step->execute([]);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');
    expect($results[0]->data['hello'])->toEqual('world');

})
    ->skip(
        !extension_loaded('v8js'),
        'v8js extension not installed'
    )
    ->skip(
        !class_exists(\Chenos\V8JsModuleLoader\ModuleLoader::class),
        'chenos/v8js-module-loader not installed'
    );

test('it can execute a file', function () {

    $step = Js::make()
        ->root($this->resources('node/package'))
        ->file('hello.js');

    $results = $step->execute([
        'name' => 'world',
    ]);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');
    expect($results[0]->data['message'])->toEqual('hello world');

})
    ->skip(
        !extension_loaded('v8js'),
        'v8js extension not installed'
    )
    ->skip(
        !class_exists(\Chenos\V8JsModuleLoader\ModuleLoader::class),
        'chenos/v8js-module-loader not installed'
    );

test('it can load a module', function () {

    $step = Js::make()
        ->root($this->resources('node/package'))
        ->file('module.js');

    $results = $step->execute([
        'words' => ['hello', 'world'],
    ]);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');
    expect($results[0]->data['message'])->toEqual('hello world');

})
    ->skip(
        !extension_loaded('v8js'),
        'v8js extension not installed'
    )
    ->skip(
        !class_exists(\Chenos\V8JsModuleLoader\ModuleLoader::class),
        'chenos/v8js-module-loader not installed'
    );
