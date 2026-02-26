<?php

use Look\Workflows\Core\Drivers\SyncDriver;
use Look\Workflows\Core\Exceptions\DriverException;
use Look\Workflows\Core\Runtime\ExecutionManager;

test('it can return a default driver', function () {

    $driver = ExecutionManager::make()->driver();

    expect($driver)->not->toBeNull();

});

test('it can return a named driver', function () {

    $manager = ExecutionManager::make();
    $manager->register('sync', SyncDriver::class);

    $driver = $manager->driver('sync');

    expect($driver)->not->toBeNull();

});

test('it can register a driver class', function () {

    $driver = ExecutionManager::make()
        ->register('sync', SyncDriver::class)
        ->driver('sync');

    expect($driver)->not->toBeNull();

});

test('it can register a driver instance', function () {

    $custom = new SyncDriver;
    $manager = ExecutionManager::make();
    $manager->register('default', $custom);

    $driver = $manager->driver();

    expect($driver)->toEqual($custom);

});

test('it can register a driver closure', function () {

    $manager = ExecutionManager::make();
    $manager
        ->register('custom', function () {
            return new SyncDriver;
        });

    $driver = $manager->driver('custom');

    expect($driver)->not->toBeNull();

});

test('it throws an exception when driver is not regisitered', function () {

    $driver = ExecutionManager::make()
        ->driver('unknown');

})->throws(DriverException::class);
