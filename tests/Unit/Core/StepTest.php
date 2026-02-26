<?php

use Look\Workflows\Core\Step;

test('steps can be constructed by type', function () {

    $step = Step::type('test:mock');

    expect($step)->not->toBeNull();

});
