<?php

namespace Look\Workflows\Huggingface;

use Look\Workflows\Core\Support\Guzzle;
use Kambo\Huggingface\Client as HuggingfaceClient;
use Kambo\Huggingface\Factory;
use Kambo\Huggingface\Huggingface as HuggingfaceBase;

class Huggingface extends HuggingfaceBase
{
    public static function client(string $apiKey): HuggingfaceClient
    {
        return self::factory()
            ->withApiKey($apiKey)
            ->make();
    }

    public static function factory(): Factory
    {
        $factory = new Factory;

        $httpClient = Guzzle::client(onlyIfMocked: true);
        if ($httpClient) {
            $factory->withHttpClient($httpClient);
        }

        return $factory;
    }
}
