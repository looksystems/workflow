<?php

namespace Look\Workflows\V8;

use Chenos\V8JsModuleLoader\ModuleLoader;
use Look\Workflows\Core\Runtime\ExecutionResult;
use Look\Workflows\Core\Steps\AbstractStep;
use Look\Workflows\Core\Support\FluentData;
use V8Js;
use V8JsException;

class Js extends AbstractStep
{
    protected ?string $modules = null;
    protected ?string $root = null;
    protected ?string $filepath = null;
    protected ?string $script = null;

    // INSTANTIATION

    public static function make(?string $script = null): self
    {
        $step = new self;

        if ($script) {
            $step->script($script);
        }

        return $step;
    }

    // PARAMETERS

    public function root(string $filepath): self
    {
        $this->root = $filepath;

        return $this;
    }

    public function modules(string $filepath): self
    {
        $this->modules = $filepath;

        return $this;
    }

    public function file(string $filepath, ?string $root = null): self
    {
        if (isset($root)) {
            $this->root = $root;
        }

        $this->filepath = $filepath;

        return $this;
    }

    public function script(string $script): self
    {
        $this->script = $script;

        return $this;
    }

    // EXECUTION

    public function apply(?FluentData $data = null, string $port = 'input')
    {
        if ($this->script) {
            $script = $this->script;
        } elseif ($this->root) {
            $filepath = $this->filepath ?? 'index.js';
            // FIXME: filepath security
            $script = file_get_contents($this->root.'/'.$filepath);
        } elseif ($this->filepath) {
            // FIXME: filepath security
            $script = file_get_contents($this->filepath);
        }

        if (empty($script)) {
            return false;
        }

        $pwd = getcwd();
        try {
            if ($this->root) {
                chdir($this->root);
            }
            if ($this->root) {
                // FIXME: filepath security
                $modules = $this->root.'/'.($this->modules ?? 'node_modules');
            } else {
                // FIXME: filepath security
                $modules = $this->modules;
            }
            $result = ExecutionResult::output([]);
            $v8 = new V8Js;
            $v8->data = $data->toArray();
            $v8->input = $port;
            if ($modules) {
                $loader = new ModuleLoader($modules);
                $loader->setExtensions('.js', '.json');
                $loader->addVendorDir($modules);

                $v8->setModuleNormaliser([$loader, 'normaliseIdentifier']);
                $v8->setModuleLoader([$loader, 'loadModule']);
            }
            $v8->output = function (array|object $data, string $port = 'output') use (&$result) {
                $result = ExecutionResult::output((array) $data, $port);
            };
            $v8->error = function (string $error, string $port = 'error') use (&$result) {
                $result = ExecutionResult::error($error, $port);
            };
            $v8->executeString($script);

        } catch (V8JsException $e) {
            $result = ExecutionResult::error($e->getJsTrace());
        } finally {
            @chdir($pwd);
        }

        return $result;
    }

    // SERIALIZATION

    public function import(array $data): void
    {
        $this->root = $data['root'] ?? null;
        $this->modules = $data['modules'] ?? null;
        $this->filepath = $data['filepath'] ?? null;
        $this->script = $data['script'] ?? null;
    }

    public function export(): array
    {
        return array_filter([
            'root' => $this->root ?? null,
            'modules' => $this->modules ?? null,
            'filepath' => $this->filepath ?? null,
            'script' => $this->script ?? null,
        ]);
    }
}
