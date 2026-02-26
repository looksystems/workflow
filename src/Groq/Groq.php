<?php

namespace Look\Workflows\Groq;

use Look\Workflows\Core\Support\WithGuzzle;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LucianoTonet\GroqPHP\Groq as GroqBase;
use Psr\Http\Message\ResponseInterface;

class Groq extends GroqBase
{
    use WithGuzzle;

    public function makeRequest(Request $request): ResponseInterface
    {
        return $this->guzzle()->client()->send($request);
    }

    // TESTING

    public static function fakeChat(string $text): Response
    {
        $json = json_encode(
            [
                'choices' => [
                    'text' => $text,
                ],
            ],
        );

        return new Response($code, ['Content-Type' => 'application/json; charset=utf-8'], $json);
    }

    public static function fakeRateLimit(): Response
    {
        $json = '{ "error": { "message": "Rate limit reached for model \"Llama3-70b-8192\" in organization org on tokens per minute (TPM): Limit 6000, Used 7198, Requested ~5273. Please try again in 1m4.714s. Visit https://console-groq.com/docs/rate-limits for more intormation.", "type": "tokens", "code": "rate_limit_exceeded" } }';

        return new Response(429, ['Content-Type' => 'application/json; charset=utf-8'], $json);
    }
}
