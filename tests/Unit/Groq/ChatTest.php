<?php

use Look\Workflows\Core\Support\Guzzle;
use Look\Workflows\Groq\Chat;
use Look\Workflows\Groq\Groq;

test('it can be constructed', function () {

    $step = new Chat;
    expect($step)->not->toBeNull();

});

test('it can chat with groq and return a message', function () {

    $snapshot = Guzzle::snapshot(
        $this->resources('guzzle/groq-hello.json')
    );

    $step = Chat::make()
        ->message('Hello!');

    $results = $step->execute([]);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');
    expect($results[0]->data->get('message'))->not->toBeEmpty();

    $snapshot->end();

});

test('it can chat with groq and append message', function () {

    $snapshot = Guzzle::snapshot(
        $this->resources('guzzle/groq-hello.json')
    );

    $step = Chat::make()
        ->message('Hello!')
        ->respondWithAllMessages();

    $results = $step->execute([]);

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');
    expect($results[0]->data->get('messages'))->toHaveCount(2);

    $snapshot->end();

});

test('it can chat with groq and return raw response', function () {

    $snapshot = Guzzle::snapshot(
        $this->resources('guzzle/groq-hello.json')
    );

    $step = Chat::make()
        ->message('Hello!')
        ->respondWithRaw();

    $results = $step->execute([]);

    $choices = $results[0]->data->get('choices');

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('output');
    expect($choices)->toHaveCount(1);
    expect($choices[0]['message']['role'])->toEqual('assistant');
    expect($choices[0]['message']['content'])->not->toBeEmpty();

    $snapshot->end();

});

test('it returns error when groq is rate limited', function () {

    Guzzle::mock(
        Groq::fakeRateLimit()
    );

    $step = Chat::make()
        ->message('Hello!')
        ->respondWithRaw();

    $results = $step->execute([]);

    $error = $results[0]->data->get('error');

    expect($results)->toHaveCount(1);
    expect($results[0]->port)->toEqual('error');

    expect($error)->not->toBeEmpty();
    expect($error['message'])->not->toBeEmpty();

});

/*
array(8) {
  ["id"]=>
  string(45) "chatcmpl-e92f5e2e-aa1b-41fe-bc85-766b0ac00402"
  ["object"]=>
  string(15) "chat.completion"
  ["created"]=>
  int(1716202940)
  ["model"]=>
  string(18) "mixtral-8x7b-32768"
  ["choices"]=>
  array(1) {
    [0]=>
    array(4) {
      ["index"]=>
      int(0)
      ["message"]=>
      array(2) {
        ["role"]=>
        string(9) "assistant"
        ["content"]=>
        string(423) "Hello! It's nice to meet you. Is there something you would like to know or talk about? I'm here to help with any questions you have to the best of my ability. You can ask me about a wide variety of topics, including general knowledge, facts, research, and writing assistance. I can also help you brainstorm ideas, create outlines, and provide writing feedback. Just let me know how I can assist you today. I'm here to help!"
      }
      ["logprobs"]=>
      NULL
      ["finish_reason"]=>
      string(4) "stop"
    }
  }
  ["usage"]=>
  array(6) {
    ["prompt_tokens"]=>
    int(12)
    ["prompt_time"]=>
    float(0.003606419)
    ["completion_tokens"]=>
    int(99)
    ["completion_time"]=>
    float(0.169076851)
    ["total_tokens"]=>
    int(111)
    ["total_time"]=>
    float(0.17268327)
  }
  ["system_fingerprint"]=>
  string(13) "fp_c5f20b5bb1"
  ["x_groq"]=>
  array(1) {
    ["id"]=>
    string(30) "req_01hyatn9jdec3vce1e8rpkwsdb"
  }
}
*/
