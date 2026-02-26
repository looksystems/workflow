<?php

namespace Look\Workflows\Laravel\Steps;

use Look\Workflows\Core\Concerns\EvaluatesExpressions;
use Look\Workflows\Core\Runtime\ExecutionResult;
use Look\Workflows\Core\Steps\AbstractStep;
use Look\Workflows\Core\Support\FluentData;
use Illuminate\Support\Facades\Http as HttpClient;

// TODO: add some kind of "session" support
// inc. cookie jars, js support, etc?

class Http extends AbstractStep
{
    use EvaluatesExpressions;

    protected ?string $url;
    protected ?string $method;
    protected ?string $token;
    protected array $auth;
    protected array $headers;
    protected array $body;
    protected array $params;
    protected bool $form;
    protected ?string $accept;
    protected int $timeout;
    protected int $retries;

    // INSTANTIATION

    public static function make(array $options = []): self
    {
        $step = new self;

        if ($options) {
            $step->import($options);
        }

        return $step;
    }

    // PARAMETERS

    public function get(string $url, ?array $params = null): self
    {
        return $this->method('get', $url, $params);
    }

    public function put(string $url, ?array $params = null): self
    {
        return $this->method('put', $url, $params);
    }

    public function patch(string $url, ?array $params = null): self
    {
        return $this->method('patch', $url, $params);
    }

    public function post(string $url, ?array $params = null): self
    {
        return $this->method('post', $url, $params);
    }

    public function delete(string $url, ?array $params = null): self
    {
        return $this->method('delete', $url, $params);
    }

    public function method(string $method, string $url, ?array $params = null): self
    {
        $this->method = $method;
        $this->url = $url;
        if (isset($params)) {
            $this->params = $params;
        }

        return $this;
    }

    public function body(string $content, ?string $mime = null): self
    {
        $this->body = [
            'content' => $content,
            'mime' => $mime,
        ];

        return $this;
    }

    public function asForm(bool $state = true): self
    {
        $this->form = $state;

        return $this;
    }

    public function accept(string $accept): self
    {
        $this->accept = $accept;

        return $this;
    }

    public function auth(string $type, ?string $username = null, ?string $password = null): self
    {
        $this->auth = [
            'type' => $type,
            'user' => $username,
            'pass' => $password,
        ];

        return $this;
    }

    public function token(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function headers(array $headers): self
    {
        $this->headers = array_merge($headers);

        return $this;
    }

    public function timeout(int $seconds): self
    {
        $this->timeout = $seconds;

        return $this;
    }

    public function retries(int $retries): self
    {
        $this->retries = $retries;

        return $this;
    }

    // EXECUTION

    public function apply(?FluentData $data = null, string $port = 'input')
    {
        if (!$this->url) {
            return [];
        }

        $method = strtolower((string) $this->method);
        if (!in_array($method, ['get', 'put', 'patch', 'post', 'delete'])) {
            return [];
        }

        $httpClient = HttpClient::retry($this->retries);

        if ($this->token) {
            $httpClient->withToken($this->token);
        }

        if ($this->auth) {
            $type = $this->auth['type'] ?? null;
            if ($type === 'digest') {
                $httpClient->withDigestAuth($this->auth['user'] ?? null, $this->auth['pass'] ?? null);
            } else {
                $httpClient->withBasicAuth($this->auth['user'] ?? null, $this->auth['pass'] ?? null);
            }
        }

        if ($this->headers) {
            $httpClient->withHeaders($this->headers);
        }

        if ($this->body) {
            $content = $this->body['content'] ?? null;
            $mime = $this->body['mime'] ?? null;
            if ($content) {
                if ($mime) {
                    $httpClient->withBody($this->body, $mime);
                } else {
                    $httpClient->withBody($this->body);
                }
            }
        }

        if ($this->accept) {
            $httpClient->accept($this->accept);
        }

        if ($this->timeout) {
            $httpClient->timeout($this->timeout);
        }

        if ($method === 'get') {
            if ($this->params) {
                $httpClient->withQueryParameters($this->params);
            }
            $response = $httpClient->get($this->url);
        } else {
            if ($method === 'post' && $this->form) {
                $httpClient->asForm();
            }
            $response = $httpClient->$method($this->url, $this->params);
        }

        $output = [
            'status' => $response->status(),
            'reason' => $response->reason(),
            'headers' => $response->headers(),
            'cookies' => $response->cookies()->toArray(),
            'body' => $response->body(),
        ];

        if (!$response->successful()) {
            return ExecutionResult::error($response->error());
        }

        $output['data'] = $response->json();

        return ExecutionResult::output($output);
    }

    // SERIALIZATION

    public function import(array $data): void
    {
        $this->url = $data['url'] ?? null;
        $this->method = $data['method'] ?? 'get';
        $this->token = $data['token'] ?? null;
        $this->auth = $data['auth'] ?? [];
        $this->headers = $data['headers'] ?? [];
        $this->body = $data['body'] ?? [];
        $this->params = $data['params'] ?? [];
        $this->form = $data['form'] ?? false;
        $this->accept = $data['accept'] ?? null;
        $this->timeout = $data['timeout'] ?? 30;
        $this->retries = $data['retries'] ?? 0;
    }

    public function export(): array
    {
        return array_filter([
            'url' => $this->url ?? null,
            'method' => $this->method ?? null,
            'token' => $this->token ?? null,
            'auth' => $this->auth ?? null,
            'headers' => $this->headers ?? null,
            'body' => $this->body ?? null,
            'params' => $this->params ?? null,
            'form' => $this->form ?? null,
            'accept' => $this->accept ?? null,
            'timeout' => $this->timeout ?? null,
            'retries' => $this->retries ?? null,
        ]);
    }
}
