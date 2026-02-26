<?php

namespace Look\Workflows\Core\Contracts;

interface DataEvaluator
{
    public function evaluate(array $data): mixed;
}
