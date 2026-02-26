<?php

namespace Look\Workflows\Aws;

use Aws\BedrockRuntime\BedrockRuntimeClient;
use Exception;
use Look\Workflows\Core\Runtime\ExecutionResult;
use Look\Workflows\Core\Steps\AbstractStep;
use Look\Workflows\Core\Support\FluentData;

/**
 * @see https://github.com/awsdocs/aws-doc-sdk-examples/tree/main/php/example_code/bedrock-runtime/#code-examples
 */
class Bedrock extends AbstractStep
{
    const RESPONSE_RAW = 'raw';
    const RESPONSE_MESSAGE = 'message';
    const RESPONSE_APPEND = 'append';

    protected string $model;
    protected string $region = 'eu-west-1';

    protected string $version = 'latest';
    protected string $profile = 'default';

    protected array $messages = [];
    protected ?float $temperature = null;
    protected ?int $maxTokens = null;
    protected ?string $responseType = null;

    // INSTANTIATION

    public static function make(string $model = 'anthropic.claude-v2', ?string $region = null): self
    {
        $step = new self;
        $step->model($model, $region);

        return $step;
    }

    // PARAMETERS

    public function model(string $model, ?string $region = null): self
    {
        $this->model = $model;
        if ($region) {
            $this->region = $region;
        }

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
        $accessKey = getenv('AWS_ACCESS_KEY_ID');
        if (!$accessKey) {
            return ExecutionResult::error('Aws access key not defined');
        }

        $secretKey = getenv('AWS_SECRET_ACCESS_KEY');
        if (!$secretKey) {
            return ExecutionResult::error('Aws access secret not defined');
        }

        $client = $this->makeClient();

        $completion = "";

        try {
            $prompt = "";
            foreach ($this->messages as $message) {
                if (($message['role'] ?? null) === 'assistant') {
                    $prompt .= "\n\nAssistant: ";
                } else {
                    $prompt .= "\n\nHuman: ";
                }
                $prompt .= $message['content'];
            }
            $prompt .= "\n\nAssistant:";

            $body = [
                'prompt' => $prompt,
                'max_tokens_to_sample' => $this->maxTokens,
                'temperature' => $this->temperature,
                'stop_sequences' => ["\n\nHuman:"],
            ];

            $response = $client->invokeModel([
                'contentType' => 'application/json',
                'body' => json_encode($body),
                'modelId' => $this->model,
            ]);

            $response_body = json_decode($response['body']);
            $completion = $response_body->completion;

            switch ($this->responseType) {
                case 'raw':
                    $result = ExecutionResult::output($response_body);
                    break;
                case 'all':
                case 'append':
                    $messages = $this->messages;
                    if ($completion) {
                        $messages[] = ['role' => 'assistant', 'content' => $completion];
                    }
                    $result = ExecutionResult::output(['messages' => $messages]);
                    break;
                case 'message':
                default:
                    $result = ExecutionResult::output(['message' => $completion]);
                    break;
            }
        } catch (Exception $exception) {
            $result = ExecutionResult::error($exception);
        }

        return $result;
    }

    protected function makeClient(): BedrockRuntimeClient
    {
        return AWS::bedrockClient([
            'region' => $this->region,
            'version' => $this->version,
            'profile' => $this->profile,
        ]);
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
            fn($value) => !is_null($value)
        );
    }
}
