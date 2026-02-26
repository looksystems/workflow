<?php

namespace Look\Workflows\Core\Support;

trait WithGuzzle
{
    protected GuzzleSession $guzzle;

    public function guzzle(): GuzzleSession
    {
        if (!isset($this->guzzle)) {
            $this->guzzle = Guzzle::session();
        }

        return $this->guzzle;
    }
}
