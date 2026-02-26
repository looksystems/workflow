<?php

use Look\Workflows\Core\Schemas\Stores\FileStore;

test('filestore can be constructed', function () {

    $store = new FileStore;

    expect($store)->not->toBeNull();

});

test('filestore can return whether an scheme exists or not', function () {

    $store = new FileStore($this->package('schemas'));

    $exists = $store->exists('data');
    expect($exists)->toBeTrue();

    $exists = $store->exists('non-existent');
    expect($exists)->toBeFalse();

});

test('filestore can load a schema', function () {

    $store = new FileStore($this->package('schemas'));

    $schema = $store->load('data');

    expect($schema)->not->toBeNull();

});

test('filestore can list available schemas', function () {

    $store = new FileStore($this->package('schemas'));

    $schemas = $store->list();

    expect($schemas)->not->toBeEmpty();

});
