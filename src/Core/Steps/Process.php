<?php

namespace Look\Workflows\Core\Steps;

use Look\Workflows\Core\Runtime\ExecutionResult;
use Look\Workflows\Core\Support\FluentData;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process as SymfonyProcess;

/**
 * @see https://symfony.com/doc/current/components/process.html
 */
class Process extends AbstractStep
{
    protected ?string $root = null;
    protected ?string $command = null;
    protected array $args = [];
    protected array $env = [];
    protected bool $json = false;

    // INSTANTIATION

    public static function make(?string $command = null, array $args = [], ?bool $json = null): self
    {
        $step = new self;

        if ($command) {
            $step->command($command, $args, $json);
        }

        return $step;
    }

    // PARAMETERS

    public function command(string $command, array $args = [], ?bool $json = null): self
    {
        $this->command = $command;
        $this->args = $args;
        if (isset($json)) {
            $this->json = $json;
        }

        return $this;
    }

    public function env(array $env): self
    {
        $this->env = array_merge($this->env, $env);

        return $this;
    }

    public function root(string $root): self
    {
        $this->root = $root;

        return $this;
    }

    // EXECUTION

    public function apply(?FluentData $data = null, string $port = 'input')
    {
        $process = new SymfonyProcess(
            array_merge([$this->command], $this->args),
            $this->root,
            $this->env
        );

        if ($this->json && $data) {
            $process->setInput(json_encode($data->toArray()));
        }

        try {
            $process->mustRun();

            if ($process->isSuccessful()) {
                $output = $process->getOutput();
                if ($this->json) {
                    $output = json_decode($output, true);
                }
                if (is_scalar($output)) {
                    $output = ['output' => $output];
                }

                $result = ExecutionResult::output($output);
            } else {
                $result = ExecutionResult::error($process->getErrorOutput());
            }
        } catch (ProcessFailedException $exception) {
            $result = ExecutionResult::error($exception);
        }

        return $result;
    }

    // SERIALIZATION

    public function import(array $data): void
    {
        $this->root = $data['root'] ?? null;
        $this->command = $data['command'] ?? null;
        $this->env = $data['env'] ?? null;
    }

    public function export(): array
    {
        return array_filter([
            'root' => $this->root ?? null,
            'command' => $this->command ?? null,
            'env' => $this->env ?? null,
        ]);
    }
}
