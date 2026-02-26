<?php

namespace Look\Workflows\Core\Support;

use Closure;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;

/**
 * @see https://docs.guzzlephp.org/en/stable/testing.html
 */
class Guzzle
{
    protected static array $sessions = [];

    public static function snapshot(?string $filepath = null, ?Closure $callback = null): GuzzleSession
    {
        $session = (new GuzzleSession)->begin($filepath);
        if ($callback) {
            try {
                $client = $session->client();
                $callback($client);
                $session->end();
            } catch (Exception $e) {
                $session->end();
                throw $e;
            }
        } else {
            self::$sessions[] = $session;
        }

        return $session;
    }

    public static function mock(Response|RequestException|array|string $responses, ?Closure $callback = null): GuzzleSession
    {
        $session = (new GuzzleSession)->push($responses);
        self::$sessions[] = $session;
        return $session;
    }

    public static function session(): GuzzleSession
    {
        if (self::$sessions) {
            return array_pop(self::$sessions);
        }

        return new GuzzleSession;
    }

    public static function client(bool $onlyIfMocked = false): ?ClientInterface
    {
        if (self::$sessions) {
            $session = array_pop(self::$sessions);
            return $session->client($onlyIfMocked);
        }

        return $onlyIfMocked ? null : new GuzzleClient;
    }
}
