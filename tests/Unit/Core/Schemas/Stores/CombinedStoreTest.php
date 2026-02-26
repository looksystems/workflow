<?php

use Look\Workflows\Core\Schemas\Stores\CombinedStore;

test('combined store can be constructed', function () {

    $store = new CombinedStore;

    expect($store)->not->toBeNull();

});

test('combined store can return whether an scheme exists or not', function () {

    $store = (new CombinedStore)
        ->addDirectory($this->package('schemas'));

    $exists = $store->exists('data');
    expect($exists)->toBeTrue();

    $exists = $store->exists('non-existent');
    expect($exists)->toBeFalse();

});

test('combined store can load a schema', function () {

    $store = (new CombinedStore)
        ->addDirectory($this->package('schemas'));

    $schema = $store->load('data');

    expect($schema)->not->toBeNull();

});

test('combined store can list available schemas', function () {

    $store = (new CombinedStore)
        ->addDirectory($this->package('schemas'));

    $schemas = $store->list();

    expect($schemas)->not->toBeEmpty();

});
