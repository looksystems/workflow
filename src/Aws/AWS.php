<?php

namespace Look\Workflows\Aws;

use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\MockHandler;
use Aws\Result;
use Aws\Exception\AwsException;

class AWS
{
    protected static ?MockHandler $mockHandler = null;

    public static function bedrockClient(array $config = []): BedrockRuntimeClient
    {
        $defaults = [
            'region' => 'eu-west-1',
            'version' => 'latest',
            'profile' => 'default',
        ];

        $config = array_merge($defaults, $config);

        if (self::$mockHandler) {
            $config['handler'] = self::$mockHandler;
            $config['credentials'] = [
                'key' => 'mock-key',
                'secret' => 'mock-secret',
            ];
            unset($config['profile']);
            self::$mockHandler = null;
        }

        return new BedrockRuntimeClient($config);
    }

    // TESTING

    public static function mockBedrock(array $response): void
    {
        if (!self::$mockHandler) {
            self::$mockHandler = new MockHandler();
        }

        // Format response to match Bedrock API structure
        $formattedResponse = [
            'body' => json_encode($response),
            'contentType' => 'application/json',
        ];

        self::$mockHandler->append(new Result($formattedResponse));
    }

    public static function mockBedrockException(string $message, string $code = 'ValidationException'): void
    {
        if (!self::$mockHandler) {
            self::$mockHandler = new MockHandler();
        }

        self::$mockHandler->append(new AwsException(
            $message,
            new \Aws\Command('InvokeModel'),
            ['code' => $code]
        ));
    }

    public static function clearMocks(): void
    {
        self::$mockHandler = null;
    }
}