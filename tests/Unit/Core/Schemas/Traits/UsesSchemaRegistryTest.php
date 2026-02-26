<?php

use Look\Workflows\Core\Schemas\SchemaRegistry;
use Look\Workflows\Core\Workflow;

test('it uses the default schema registry', function () {

    $workflow = Workflow::make();

    expect($workflow->schemas())
        ->toEqual(SchemaRegistry::default());

});

test('it can use a custom schema registry', function () {

    $custom = new SchemaRegistry;

    $workflow = Workflow::make()
        ->useSchemaRegistry($custom);

    expect($workflow->schemas())
        ->toEqual($custom);

});

test('it can use a custom via closure schema registry', function () {

    $custom = new SchemaRegistry;

    $workflow = Workflow::make()
        ->useSchemaRegistry(function () use ($custom) {
            return $custom;
        });

    expect($workflow->schemas())
        ->toEqual($custom);

});
