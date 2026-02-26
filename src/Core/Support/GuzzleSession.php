<?php

namespace Look\Workflows\Core\Support;

use Closure;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Response;

/**
 * @see https://docs.guzzlephp.org/en/stable/testing.html
 */
class GuzzleSession
{
    protected Closure|string|bool|null $condition = null;
    protected ?string $filepath = null;
    protected bool $record = false;
    protected array $history = [];

    public static function make(): self
    {
        return new self;
    }

    // ONLY

    public function onlyIf(Closure|string|bool $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    // HTTP CLIENT

    public function client(bool $onlyIfMocked = false): ?ClientInterface
    {
        if (!$this->canMock()) {
            return $onlyMocked ? null : new GuzzleClient;
        }

        if ($this->filepath) {
            $this->load($this->filepath, throwIfNotFound: false);
        }

        if ($this->history) {
            return $this->makeReplayClient();
        }

        return $this->makeHistoryClient();
    }

    public function mocked(): ?ClientInterface
    {
        return $this->client(onlyIfMocked: true);
    }

    public function canMock(): bool
    {
        if (isset($this->condition)) {
            if ($this->condition instanceof Closure) {
                return (bool) call_user_func($this->condition);
            } elseif (is_string($this->condition)) {
                return getenv($this->condition) === 'mock';
            }

            return $this->condition;
        }

        return $this->history || $this->record;
    }

    protected function makeReplayClient(): ClientInterface
    {
        $responses = [];
        foreach ($this->history as $history) {
            if (!empty($history['response'])) {
                $responses[] = $history['response'];
            } elseif (!empty($history['error'])) {
                $responses[] = $history['error'];
            }
        }

        return new GuzzleClient([
            'handler' => HandlerStack::create(new MockHandler($responses)),
        ]);
    }

    protected function makeHistoryClient(): ClientInterface
    {
        $handler = HandlerStack::create();
        $handler->push(Middleware::history($this->history));

        return new GuzzleClient([
            'handler' => $handler,
        ]);
    }

    // SNAPSHOT

    public function begin(?string $filepath = null): self
    {
        $this->filepath = $filepath;
        $this->record = true;

        return $this;
    }

    public function end(): self
    {
        $this->record = false;
        if ($this->filepath) {
            $this->save($this->filepath);
        }

        return $this;
    }

    public function load(string $filepath, bool $throwIfNotFound = true): self
    {
        if (file_exists($filepath)) {
            $data = json_decode(file_get_contents($filepath), true);

            $this->history = [];
            foreach ($data as $record) {
                $history = [];
                if (!empty($record['response'])) {
                    $history['response'] = Message::parseResponse($record['response']);

                }
                $this->history[] = $history;
            }
        } elseif (!$throwIfNotFound) {
            $this->filepath = $filepath;
        } else {
            throw new Exception("Snaphot '$filepath' not found");
        }

        return $this;
    }

    public function save(string $filepath): self
    {
        if (empty($this->history)) {
            return $this;
        }

        $data = [];
        foreach ($this->history as $history) {
            $record = [];
            if (!empty($history['response'])) {
                $record['response'] = Message::toString($history['response']);
            }
            $data[] = $record;
        }

        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));

        return $this;
    }

    public function push(Response|RequestException|array|string $response): self
    {
        if (is_array($response)) {
            foreach ($response as $r) {
                $this->push($r);
            }
        } elseif ($response instanceof Response) {
            $this->history[] = ['response' => $response];
        } elseif ($response instanceof RequestException) {
            $this->history[] = ['error' => $response];
        } else {
            $this->history[] = new Response(200, ['Content-Type' => 'application/json; charset=utf-8'], $response);
        }

        return $this;
    }

    public function history(): array
    {
        return $this->history;
    }

    public function flush(): self
    {
        $this->history = [];

        return $this;
    }

    public function reset(): self
    {
        $this->condition = null;
        $this->filepath = null;
        $this->record = false;
        $this->history = [];

        return $this;
    }
}
