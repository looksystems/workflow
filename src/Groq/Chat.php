<?php

namespace Look\Workflows\Groq;

use Exception;
use Look\Workflows\Core\Runtime\ExecutionResult;
use Look\Workflows\Core\Steps\AbstractStep;
use Look\Workflows\Core\Support\FluentData;

/**
 * @see https://github.com/lucianotonet/groq-php
 */
class Chat extends AbstractStep
{
    const RESPONSE_RAW = 'raw';
    const RESPONSE_MESSAGE = 'message';
    const RESPONSE_APPEND = 'append';

    protected string $model;
    protected array $messages = [];
    protected ?float $temperature = null;
    protected ?int $maxTokens = null;
    protected ?string $responseType = null;

    // INSTANTIATION

    public static function make(string $model = 'mixtral-8x7b-32768'): self
    {
        $step = new self;
        $step->model($model);

        return $step;
    }

    // PARAMETERS

    public function model(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function messages(array $messages): self
    {
        $this->messages = array_merge($this->messages, $messages);

        return $this;
    }

    public function message(string $content, string $role = 'user'): self
    {
        $this->messages[] = ['role' => $role, 'content' => $content];

        return $this;
    }

    public function temperature(float $temperature): self
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function maxTokens(int $maxTokens): self
    {
        $this->maxTokens = $maxTokens;

        return $this;
    }

    public function respondWithRaw(): self
    {
        return $this->respondWith('raw');
    }

    public function respondWithMessage(): self
    {
        return $this->respondWith('message');
    }

    public function respondWithAllMessages(): self
    {
        return $this->respondWith('append');
    }

    public function respondWith(string $type): self
    {
        $this->responseType = $type;

        return $this;
    }

    // EXECUTION

    public function apply(?FluentData $data = null, string $port = 'input')
    {
        // FIXME: get key from credentials api (not yet created)
        $apiKey = getenv('GROQ_API_KEY');
        if (!$apiKey) {
            return ExecutionResult::error('Groq API key not defined');
        }

        $client = new Groq($apiKey);

        $request = [
            'model' => $this->model ?? 'mixtral-8x7b-32768',
            'messages' => $this->messages,
        ];

        if (isset($this->temperature)) {
            $request['temperature'] = $this->temperature;
        }

        if (isset($this->maxTokens)) {
            $request['max_tokens'] = $this->maxTokens;
        }

        try {
            $response = $client->chat()->completions()->create($request);

            switch ($this->responseType) {
                case 'raw':
                    $result = ExecutionResult::output($response);
                    break;
                case 'all':
                case 'append':
                    $message = $response['choices'][0]['message']['content'];
                    $messages = $this->messages;
                    if ($message) {
                        $messages[] = ['role' => 'agent', 'content' => $message];
                    }
                    $result = ExecutionResult::output(['messages' => $messages]);
                    break;
                case 'message':
                default:
                    $message = $response['choices'][0]['message']['content'];
                    $result = ExecutionResult::output(['message' => $message]);
                    break;
            }
        } catch (Exception $exception) {
            $result = ExecutionResult::error($exception);
        }

        return $result;
    }

    // SERIALIZATION

    public function import(array $data): void
    {
        $this->model = $data['model'] ?? null;
        $this->messages = $data['messages'] ?? [];
        $this->temperature = $data['temperature'] ?? null;
        $this->maxTokens = $data['maxTokens'] ?? null;
        $this->responseType = $data['responseType'] ?? null;
    }

    public function export(): array
    {
        return array_filter(
            [
                'model' => $this->model ?? null,
                'messages' => $this->messages ?? null,
                'temperature' => $this->temperature ?? null,
                'maxTokens' => $this->maxTokens ?? null,
                'responseType' => $this->responseType ?: null,
            ],
            'is_null'
        );
    }
}
