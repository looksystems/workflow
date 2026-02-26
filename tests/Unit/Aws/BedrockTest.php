<?php

use Look\Workflows\Aws\AWS;
use Look\Workflows\Aws\Bedrock;
use Look\Workflows\Core\Runtime\ExecutionResult;

test('it can be constructed', function () {
    $step = new Bedrock;
    expect($step)->not->toBeNull();
});

test('it can be configured with fluent methods', function () {
    $step = Bedrock::make('anthropic.claude-v2', 'us-east-1')
        ->message('Hello!', 'user')
        ->temperature(0.7)
        ->maxTokens(1000)
        ->respondWithMessage();

    $exported = $step->export();

    expect($exported['model'])->toBe('anthropic.claude-v2');
    expect($exported['messages'])->toHaveCount(1);
    expect($exported['messages'][0])->toBe(['role' => 'user', 'content' => 'Hello!']);
    expect($exported['temperature'])->toBe(0.7);
    expect($exported['maxTokens'])->toBe(1000);
    expect($exported['responseType'])->toBe('message');
});

test('it can add multiple messages', function () {
    $step = Bedrock::make()
        ->messages([
            ['role' => 'user', 'content' => 'What is 2+2?'],
            ['role' => 'assistant', 'content' => '4'],
        ])
        ->message('What is 3+3?', 'user');

    $exported = $step->export();

    expect($exported['messages'])->toHaveCount(3);
    expect($exported['messages'][2])->toBe(['role' => 'user', 'content' => 'What is 3+3?']);
});

test('it supports different response types', function () {
    $step1 = Bedrock::make()->respondWithRaw();
    $step2 = Bedrock::make()->respondWithMessage();
    $step3 = Bedrock::make()->respondWithAllMessages();

    expect($step1->export()['responseType'])->toBe('raw');
    expect($step2->export()['responseType'])->toBe('message');
    expect($step3->export()['responseType'])->toBe('append');
});

test('it can import and export configuration', function () {
    $data = [
        'model' => 'anthropic.claude-v2',
        'messages' => [['role' => 'user', 'content' => 'Test']],
        'temperature' => 0.5,
        'maxTokens' => 500,
        'responseType' => 'message',
    ];

    $step = new Bedrock;
    $step->import($data);
    $exported = $step->export();

    expect($exported)->toBe($data);
});

test('it returns error when AWS credentials are missing', function () {
    // Temporarily unset AWS credentials
    $accessKey = getenv('AWS_ACCESS_KEY_ID');
    $secretKey = getenv('AWS_SECRET_ACCESS_KEY');
    
    putenv('AWS_ACCESS_KEY_ID=');
    putenv('AWS_SECRET_ACCESS_KEY=');

    $step = Bedrock::make()->message('Hello!');
    $results = $step->execute();
    $result = $results[0];

    expect($result->port)->toBe('error');
    expect($result->data->get('error.message'))->toContain('Aws access');

    // Restore credentials
    putenv("AWS_ACCESS_KEY_ID=$accessKey");
    putenv("AWS_SECRET_ACCESS_KEY=$secretKey");
});

test('it can mock bedrock responses', function () {
    if ($this->shouldMockHttp('AWS_ACCESS_KEY_ID')) {
        AWS::mockBedrock([
            'completion' => 'Hello, I am Claude'
        ]);
    }

    $step = Bedrock::make('anthropic.claude-v2')
        ->message('Say hello')
        ->temperature(0.1)
        ->maxTokens(50)
        ->respondWithMessage();

    $results = $step->execute();
    $result = $results[0];

    expect($result->port)->toBe('output');
    expect($result->data->get('message'))->toBe('Hello, I am Claude');
});

test('it can mock bedrock responses with all messages', function () {
    if ($this->shouldMockHttp('AWS_ACCESS_KEY_ID')) {
        AWS::mockBedrock([
            'completion' => 'I can help with that!'
        ]);
    }

    $step = Bedrock::make('anthropic.claude-v2')
        ->message('Can you help?', 'user')
        ->respondWithAllMessages();

    $results = $step->execute();
    $result = $results[0];

    expect($result->port)->toBe('output');
    expect($result->data->get('messages'))->toHaveCount(2);
    expect($result->data->get('messages.1'))->toBe([
        'role' => 'assistant',
        'content' => 'I can help with that!'
    ]);
});

test('it can mock bedrock exceptions', function () {
    if ($this->shouldMockHttp('AWS_ACCESS_KEY_ID')) {
        AWS::mockBedrockException('Model not found', 'ResourceNotFoundException');
    }

    $step = Bedrock::make('invalid-model')
        ->message('Hello');

    $results = $step->execute();
    $result = $results[0];

    expect($result->port)->toBe('error');
    expect($result->data->get('error.message'))->toContain('Model not found');
});

// integration test - skipped by default as it makes real API calls
test('it can chat with bedrock and return a message', function () {
    $step = Bedrock::make('anthropic.claude-v2')
        ->message('Say "Hello, I am Claude" and nothing else.')
        ->temperature(0.1)
        ->maxTokens(50)
        ->respondWithMessage();

    $results = $step->execute();
    $result = $results[0];

    expect($result->port)->toBe('output');
    expect($result->data->get('message'))->toContain('Hello');
})->skip('Integration test - requires AWS credentials and API access');