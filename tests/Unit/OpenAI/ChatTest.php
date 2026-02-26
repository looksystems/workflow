<?php

use Look\Workflows\OpenAI\Chat;
use Look\Workflows\OpenAI\OpenAI;

test('it can be constructed', function () {

    $step = new Chat;
    expect($step)->not->toBeNull();

});

test('it can chat with openai and return a message', function () {

    if ($this->shouldMockHttp('OPENAI_API_KEY')) {
        OpenAI::fakeChat('Hello! How can I assist you today?');
    }

    $step = Chat::make()
        ->message('Hello!');

    $results = $step->execute([]);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');
    expect($results[0]->data['message'])->not->toBeEmpty();

});

test('it can chat with openai and append message', function () {

    if ($this->shouldMockHttp('OPENAI_API_KEY')) {
        OpenAI::fakeChat('Hello! How can I assist you today?');
    }

    $step = Chat::make()
        ->message('Hello!')
        ->respondWithAllMessages();

    $results = $step->execute([]);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');
    expect($results[0]->data['messages'])->toHaveCount(2);

});

test('it can chat with openai and return raw response', function () {

    if ($this->shouldMockHttp('OPENAI_API_KEY')) {
        OpenAI::fakeChat('Hello! How can I assist you today?');
    }

    $step = Chat::make()
        ->message('Hello!')
        ->respondWithRaw();

    $results = $step->execute([]);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');
    expect($results[0]->data['choices'])->toHaveCount(1);
    expect($results[0]->data['choices'][0]['message']['role'])->toEqual('assistant');
    expect($results[0]->data['choices'][0]['message']['content'])->not->toBeEmpty();

});

/*
array(1) {
  [0]=>
  object(Look\Workflows\Core\Runtime\ExecutionResult)#596 (2) {
    ["port"]=>
    string(6) "output"
    ["data"]=>
    array(6) {
      ["id"]=>
      string(38) "chatcmpl-9QttKJlNMWWgMxsp31F1zdXgXPPqT"
      ["object"]=>
      string(15) "chat.completion"
      ["created"]=>
      int(1716198402)
      ["model"]=>
      string(18) "gpt-3.5-turbo-0125"
      ["choices"]=>
      array(1) {
        [0]=>
        array(3) {
          ["index"]=>
          int(0)
          ["message"]=>
          array(2) {
            ["role"]=>
            string(9) "assistant"
            ["content"]=>
            string(34) "Hello! How can I assist you today?"
          }
          ["finish_reason"]=>
          string(4) "stop"
        }
      }
      ["usage"]=>
      array(3) {
        ["prompt_tokens"]=>
        int(9)
        ["completion_tokens"]=>
        int(9)
        ["total_tokens"]=>
        int(18)
      }
    }
  }
}
*/
