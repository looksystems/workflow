<?php

use Look\Workflows\Laravel\Steps\Sql;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    $schema = Schema::connection(null);
    $schema->create('test', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable()->index();
        $table->string('email')->nullable()->index();
        $table->timestamps();
    });

    for ($i = 0; $i < 10; $i++) {
        DB::table('test')->insert([
            'name' => fake()->name(),
            'email' => fake()->email(),
        ]);
    }
});

test('it can be constructed', function () {

    $step = new Sql;
    expect($step)->not->toBeNull();

});

test('it can execute a query', function () {

    $step = Sql::make()
        ->query('SELECT * FROM test');

    $results = $step->execute([]);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');
    expect($results[0]->data['data'])->not->toBeEmpty();

});
