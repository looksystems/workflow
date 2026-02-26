<?php

namespace Look\Workflows\Google;

use Look\Workflows\Core\Runtime\ExecutionResult;
use Look\Workflows\Core\Steps\AbstractStep;
use Look\Workflows\Core\Support\FluentData;
use Look\Workflows\Core\Support\WithGuzzle;
use Google\Client as GoogleClient;
use Google\Service\CustomSearchAPI;
use Google\Service\Exception;

/**
 * @see https://developers.google.com/custom-search/v1/reference/rest/v1/cse/list#request
 * @see https://code.google.com/apis/console?api=customsearch (to generate cse id)
 * @see https://github.com/googleapis/google-api-php-client
 */
class Customsearch extends AbstractStep
{
    use WithGuzzle;

    protected string $applicationName = 'customsearch';

    protected ?string $query = null;
    protected array $params = [];

    // INSTANTIATION

    public static function make(?string $query = null, array $params = []): self
    {
        $step = new self;
        if ($query) {
            $step->query($query, $params);
        }

        return $step;
    }

    // PARAMETERS

    public function query(string $query, array $params = []): self
    {
        $this->query = $query;
        if ($this->params) {
            $this->params = $params;
        }

        return $this;
    }

    public function addParam(string $key, $value): self
    {
        $this->params[$key] = $value;

        return $this;
    }

    public function addParams(array $params): self
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    public function __call(string $method, array $args): mixed
    {
        $value = $args[0] ?? null;
        if (is_null($value)) {
            return $this->params[$method] ?? null;
        }

        $this->params[$method] = $value;

        return $this;
    }

    // EXECUTION

    /**
     * @throws Exception
     */
    public function apply(?FluentData $data = null, string $port = 'input')
    {
        if (!$this->query) {
            return [];
        }

        // FIXME: check class exists
        // FIXME: get key from credentials api (not yet created)
        $apiKey = getenv('GOOGLE_API_KEY');
        if (!$apiKey) {
            return ExecutionResult::error('Google API key not defined');
        }

        $engineId = getenv('GOOGLE_CSE_ID');
        if (!$engineId) {
            return ExecutionResult::error('Google custom search engine id not defined');
        }

        $client = $this->makeClient($apiKey, $this->applicationName);

        $params = $this->params;
        $params['cx'] = $engineId;
        $params['q'] = $this->query;

        $service = new CustomSearchAPI($client);
        $results = $service->cse->listCse($params);

        $items = [];
        foreach ($results->getItems() as $item) {
            $items[] = $item->toSimpleObject();
        }

        return ExecutionResult::output(['items' => $items]);
    }

    protected function makeClient(string $apiKey, string $applicationName): GoogleClient
    {
        $client = new GoogleClient;
        $client->setApplicationName($applicationName);
        $client->setDeveloperKey($apiKey);

        $httpClient = $this->guzzle()->client(onlyIfMocked: true);
        if ($httpClient) {
            $client->setHttpClient($httpClient);
        }

        return $client;
    }

    // SERIALIZATION

    public function import(array $data): void
    {
        $this->query = $data['query'] ?? null;
        $this->params = $data['params'] ?? [];
    }

    public function export(): array
    {
        return array_filter(
            [
                'query' => $this->query ?? null,
                'params' => $this->params ?? null,
            ],
            'is_null'
        );
    }
}
