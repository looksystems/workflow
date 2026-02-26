<?php

use Look\Workflows\Core\Support\Guzzle;
use Look\Workflows\Huggingface\Inference;

test('it can be constructed', function () {

    $step = new Inference;
    expect($step)->not->toBeNull();

});

test('it can generate text', function () {

    $snapshot = Guzzle::snapshot($this->resources('guzzle/huggingface-generate.json'));

    $step = Inference::make('gpt2')
        ->generate('The goal of life is?');

    $results = $step->execute([]);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');
    expect($results[0]->data->get('generated_text'))->not->toBeEmpty();

    $snapshot->end();

});

test('it can fill masked text', function () {

    $snapshot = Guzzle::snapshot($this->resources('guzzle/huggingface-fill.json'));

    $step = Inference::make('distilbert-base-uncased')
        ->fill('The answer to the universe is [MASK].');

    $results = $step->execute([]);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');
    expect($results[0]->data->get('filled_masks'))->not->toBeEmpty();

    $snapshot->end();

});

/*
array(1) {
  [0]=>
  object(Look\Workflows\Core\Runtime\ExecutionResult)#600 (2) {
    ["port"]=>
    string(6) "output"
    ["data"]=>
    array(2) {
      ["type"]=>
      enum(Kambo\Huggingface\Enums\Type::TEXT_GENERATION)
      ["generated_text"]=>
      string(173) "The goal of life is? As long as there's no death, as long as we don't go to hell, as long as we go to hell at the same time as they get there," she said.

When he goes home,"
    }
  }
}
*/

/*

array(1) {
  [0]=>
  object(Look\Workflows\Core\Runtime\ExecutionResult)#578 (2) {
    ["port"]=>
    string(6) "output"
    ["data"]=>
    array(2) {
      ["type"]=>
      enum(Kambo\Huggingface\Enums\Type::FILL_MASK)
      ["filled_masks"]=>
      array(5) {
        [0]=>
        array(4) {
          ["score"]=>
          float(0.1537376344203949)
          ["token"]=>
          int(4242)
          ["token_str"]=>
          string(7) "unknown"
          ["sequence"]=>
          string(38) "the answer to the universe is unknown."
        }
        [1]=>
        array(4) {
          ["score"]=>
          float(0.01927444152534008)
          ["token"]=>
          int(23077)
          ["token_str"]=>
          string(7) "entropy"
          ["sequence"]=>
          string(38) "the answer to the universe is entropy."
        }
        [2]=>
        array(4) {
          ["score"]=>
          float(0.016537245362997055)
          ["token"]=>
          int(10709)
          ["token_str"]=>
          string(8) "infinite"
          ["sequence"]=>
          string(39) "the answer to the universe is infinite."
        }
        [3]=>
        array(4) {
          ["score"]=>
          float(0.015316959470510483)
          ["token"]=>
          int(5717)
          ["token_str"]=>
          string(4) "zero"
          ["sequence"]=>
          string(35) "the answer to the universe is zero."
        }
        [4]=>
        array(4) {
          ["score"]=>
          float(0.013508901931345463)
          ["token"]=>
          int(15579)
          ["token_str"]=>
          string(8) "infinity"
          ["sequence"]=>
          string(39) "the answer to the universe is infinity."
        }
      }
    }
  }
}
*/
