<?php

namespace Look\Workflows\Huggingface;

use Look\Workflows\Core\Runtime\ExecutionResult;
use Look\Workflows\Core\Steps\AbstractStep;
use Look\Workflows\Core\Support\FluentData;
use Kambo\Huggingface\Client;
use Kambo\Huggingface\Enums\Type;

/**
 * @see https://github.com/kambo-1st/huggingface-php
 * @see https://huggingface.co/inference-api/serverless
 * @see https://huggingface.co/docs/api-inference/index
 */
class Inference extends AbstractStep
{
    protected string $model;
    protected array|string $inputs = [];
    protected ?float $temperature = null;
    protected ?int $maxTokens = null;
    protected ?string $responseType = null;
    protected Type $type = Type::TEXT_GENERATION;

    // INSTANTIATION

    public static function make(?string $model = null): self
    {
        $step = new self;
        if ($model) {
            $step->model($model);
        }

        return $step;
    }

    // PARAMETERS

    public function model(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function generate(array|string $inputs): self
    {
        $this->type = Type::TEXT_GENERATION;
        $this->inputs = $inputs;

        return $this;
    }

    public function fill(array|string $inputs): self
    {
        $this->type = Type::FILL_MASK;
        $this->inputs = $inputs;

        return $this;
    }

    // EXECUTION

    public function apply(?FluentData $data = null, string $port = 'input')
    {
        // FIXME: get key from credentials api (not yet created)
        $apiKey = getenv('HUGGINGFACE_API_KEY');
        if (!$apiKey) {
            return ExecutionResult::error('Huggingface API key not defined');
        }

        // TODO: remove dependency on library
        // and implement http client, along with more query types
        $client = Huggingface::client($apiKey);

        $response = $client->inference()->create([
            'model' => $this->model,
            'inputs' => $this->inputs,
            'type' => $this->type,
        ]);

        return ExecutionResult::output($response->toArray());
    }

    // SERIALIZATION

    public function import(array $data): void
    {
        $this->model = $data['model'] ?? null;
        $this->inputs = $data['inputs'] ?? [];
        $this->type = $data['type'] ?? Type::TEXT_GENERATION;
    }

    public function export(): array
    {
        return array_filter(
            [
                'model' => $this->model ?? null,
                'inputs' => $this->inputs ?? null,
                'type' => $this->type ?? null,
            ],
            'is_null'
        );
    }
}
