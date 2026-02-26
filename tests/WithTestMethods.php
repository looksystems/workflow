<?php

namespace Tests;

trait WithTestMethods
{
    protected function shouldMockHttp(?string $apiKey = null): bool
    {
        if ((bool) getenv('HTTP_FAKE')) {
            return true;
        }

        if ($apiKey) {
            $value = getenv($apiKey);

            return ! $value || $value === 'mock';
        }

        return false;
    }

    protected function package(?string $path = null): string
    {
        return realpath(__DIR__.'/..').'/'.$path;
    }

    protected function resources(?string $path = null): string
    {
        return $this->testpath('resources/'.$path);
    }

    protected function loadResource(string $path): string
    {
        return file_get_contents($this->resources($path));
    }

    protected function testpath(?string $path = null): string
    {
        return __DIR__.'/'.$path;
    }

}
