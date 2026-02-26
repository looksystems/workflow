<?php

namespace Look\Workflows\Core\Schemas\Stores;

use Exception;
use Symfony\Component\Yaml\Yaml;

class FileStore extends AbstractStore
{
    protected string $path;

    protected bool $useCache = true;

    protected bool $readOnly = true;

    protected string $indexFile = '_index';

    protected array $extensions = ['json', 'yaml', 'php'];

    protected ?string $cacheFile = null;

    protected ?array $cache = null;

    // INSTANTIATION

    public function __construct(?string $path = null)
    {
        if ($path) {
            $this->setPath($path);
        }
    }

    // FILEPATH

    public function setPath(string $path): self
    {
        $this->path = realpath(rtrim($path, '/'));

        return $this;
    }

    // SCHEMA STORE

    /**
     * @throws Exception
     */
    public function exists(string $name): bool
    {
        $index = $this->loadIndex();

        return in_array($name, $index);
    }

    protected function fetch(string $name): ?array
    {
        $filepath = $this->findFile(
            $this->getKeyPath($name)
        );

        if (!$filepath) {
            return null;
        }

        $data = $this->loadFile($filepath);
        if (!$data) {
            return null;
        }

        $data['name'] = $name;

        return $data;
    }

    /**
     * @throws Exception
     */
    public function list(): array
    {
        return $this->loadIndex();
    }

    protected function getKeyPath(string $name): string
    {
        return $this->path.'/'.ltrim(str_replace('.', '/', $name), '/');
    }

    /**
     * @throws Exception
     */
    protected function loadIndex($useCache = null): array
    {
        if (!$this->path) {
            return [];
        }

        if (!isset($useCache)) {
            $useCache = $this->useCache;
        }

        if ($useCache) {
            if (isset($this->cache)) {
                return $this->cache;
            }

            $this->cacheFile = $this->findFile($this->path.'/.index');
            if ($this->cacheFile) {
                $this->cache = $this->loadFile($this->cacheFile);
                if ($this->cache) {
                    return $this->cache;
                }
            }
        }

        $this->cache = [];
        $baseLen = 1 + strlen($this->path);
        $filepaths = glob($this->path.'/*');
        foreach ($filepaths as $filepath) {
            $ext = pathinfo($filepath, PATHINFO_EXTENSION);
            if (!in_array($ext, $this->extensions)) {
                continue;
            }

            $name = ltrim(str_replace('/', '.', substr($filepath, $baseLen, -strlen($ext) - 1)));

            $this->cache[] = $name;
        }

        $this->saveIndex();

        return $this->cache;
    }

    /**
     * @throws Exception
     */
    protected function saveIndex(): void
    {
        if (!$this->path || $this->readOnly) {
            return;
        }

        if (!$this->cacheFile) {
            $this->cacheFile = $this->findFile($this->path.'/.index', $this->extensions[0] ?? 'json');
        }

        $this->saveFile($this->cacheFile, $this->cache);
    }

    protected function findFile($filepath, $default = null): ?string
    {
        if (is_dir($filepath)) {
            $filepath = rtrim($filepath, '/').'/'.$this->indexFile;
        }

        foreach ($this->extensions as $ext) {
            $fullpath = $filepath.'.'.$ext;
            if (file_exists($fullpath)) {
                return $fullpath;
            }
        }

        return $default ? $filepath.'.'.$default : null;
    }

    protected function loadFile($filepath)
    {
        if (!$this->path) {
            return [];
        }

        $filepath = realpath($filepath);
        if (
            !$filepath
            || strncmp($filepath, $this->path.'/', 1 + strlen($this->path)) !== 0
        ) {
            return [];
        }

        if (is_dir($filepath)) {
            $filepath = rtrim($filepath, '/').'/'.$this->indexFile;
        }

        if (!file_exists($filepath)) {
            return [];
        }

        $ext = pathinfo($filepath, PATHINFO_EXTENSION);
        $decodeMethod = 'decode'.ucfirst(strtolower($ext));
        if (!method_exists($this, $decodeMethod)) {
            return [];
        }

        $data = $this->$decodeMethod($filepath);

        return $data ?: [];
    }

    /**
     * @throws Exception
     */
    protected function saveFile(string $filepath, array $data): void
    {
        if (!$this->path) {
            throw new Exception('Store path is not defined');
        }

        if ($this->readOnly) {
            throw new Exception('Store is read only');
        }

        $filepath = realpath($filepath);

        if ($filepath && is_dir($filepath)) {
            $filepath = rtrim($filepath, '/').'/'.$this->indexFile;
        }

        $ext = pathinfo($filepath, PATHINFO_EXTENSION);
        if (
            !$filepath
            || strncmp($filepath, $this->path.'/', 1 + strlen($this->path)) !== 0
            || !in_array($ext, $this->extensions)
        ) {
            throw new Exception('Invalid filepath for document');
        }

        $encodeMethod = 'encode'.ucfirst(strtolower($ext));
        if (!method_exists($this, $encodeMethod)) {
            throw new Exception("Unable to encode $ext file");
        }

        $filepath = substr($filepath, 0, -strlen($ext) - 1);
        foreach ($this->extensions as $ext) {
            unlink($filepath.'.'.$ext);
        }

        $this->$encodeMethod($filepath, $data);
    }

    protected function decodeJson(string $filepath): array
    {
        $data = file_get_contents($filepath);

        return @json_decode($data, true);
    }

    protected function encodeJson(string $filepath, array $data): void
    {
        $encoded = @json_encode($data);
        file_put_contents($filepath, $encoded);
    }

    protected function decodeYaml(string $filepath): array
    {
        if (function_exists('yaml_parse_file')) {
            return @yaml_parse_file($filepath);
        }

        return Yaml::parseFile($filepath);
    }

    protected function encodeYaml(string $filepath, array $data): void
    {
        if (function_exists('yaml_emit')) {
            $encoded = @yaml_emit($data);
        } else {
            $encoded = Yaml::dump($data);
        }

        file_put_contents($filepath, $encoded);
    }

    protected function decodePhp(string $filepath): array
    {
        $data = require $filepath;

        return is_array($data) ? $data : [];
    }

    protected function encodePhp(string $filepath, array $data): void
    {
        $encoded = @var_export($data, true);
        file_put_contents($filepath, $encoded);
    }
}
