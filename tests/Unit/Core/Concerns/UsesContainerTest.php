<?php

use Look\Workflows\Core\Workflow;

test('it can use a psr service container interface', function () {

    $container = app();

    $workflow = Workflow::make()
        ->setContainer($container);

    expect($workflow->getContainer())
        ->toEqual($container);

});
