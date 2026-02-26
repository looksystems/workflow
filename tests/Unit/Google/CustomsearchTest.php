<?php

use Look\Workflows\Core\Support\Guzzle;
use Look\Workflows\Google\Customsearch;

test('it can be constructed', function () {

    $step = new Customsearch;
    expect($step)->not->toBeNull();

})
    ->skip(
        !class_exists(\Google\Client::class),
        'google/apiclient not installed'
    );

test('it can search google and return results', function () {

    $snapshot = Guzzle::snapshot($this->resources('guzzle/google-customsearch'));

    $step = Customsearch::make('PSI Global');

    $results = $step->execute([]);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');
    expect($results[0]->data['items'])->toHaveCount(10);
    expect($results[0]->data['items'][0]->title)->not->toBeNull();
    expect($results[0]->data['items'][0]->snippet)->not->toBeNull();
    expect($results[0]->data['items'][0]->link)->not->toBeNull();

    $snapshot->end();

})
    ->skip(!getenv('GOOGLE_API_KEY'), 'google api key not defined')
    ->skip(!getenv('GOOGLE_CSE_ID'), 'cusom search engine id not defined');
