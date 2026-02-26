<?php

namespace Look\Workflows\OpenAI;

use OpenAI\Contracts\ClientContract;
use OpenAI\Exceptions\ErrorException;
use OpenAI\Factory;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Testing\ClientFake;

class OpenAI
{
    protected static array $fake = [];

    public static function client(string $apiKey, ?string $apiOrganisation = null, string $baseUri = 'api.openai.com/v1'): ClientContract
    {
        if (self::$fake) {
            $client = new ClientFake(self::$fake);
            self::$fake = [];

            return $client;
        }

        return (new Factory)
            ->withApiKey($apiKey)
            ->withOrganization($apiOrganisation)
            ->withBaseUri($baseUri)
            ->withHttpHeader('OpenAI-Beta', 'assistants=v1')
            ->make();
    }

    // TESTING

    public static function fake(array $responses, bool $append = true): void
    {
        if (!$append) {
            self::$fake = [];
        }

        self::$fake[] = CreateResponse::fake($responses);
    }

    public static function fakeChat(string $text, bool $append = true): void
    {
        self::fake(
            [
                'choices' => [
                    'text' => 'Hello! How can I assist you today?',
                ],
            ],
            $append
        );
    }

    public static function fakeException(array $exception, bool $append = true): void
    {
        if (!$append) {
            self::$fake = [];
        }

        self::$fake[] = new ErrorException($exception);
    }

}
