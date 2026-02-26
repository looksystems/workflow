<?php

namespace Look\Workflows\Core;

use Look\Workflows\Core\Contracts\Step as StepContract;
use Look\Workflows\Core\Exceptions\InvalidStep;
use Look\Workflows\Core\Schemas\SchemaRegistry;
use Look\Workflows\Core\Support\TypeFinder;

class Step
{
    protected static TypeFinder $typeFinder;

    /**
     * @throws InvalidStep
     */
    public static function make(array $step = [], SchemaRegistry|string $registry = 'default'): StepContract
    {
        if (!empty($step['schema'])) {
            $instance = static::schema($step['schema']);
        } elseif (!empty($step['type'])) {
            $instance = static::type($step['type']);
        } elseif (!empty($step['class'])) {
            $stepClass = $step['class'];
            $instance = new $stepClass;
        }

        if (!isset($instance)) {
            throw new InvalidStep('Invalid step');
        }

        if (!empty($step['uuid'])) {
            $instance->setUuid($step['uuid']);
        }

        if (!empty($step['name'])) {
            $instance->setName($step['name']);
        }

        if (!empty($step['data']) && is_array($step['data'])) {
            $instance->import($step['data']);
        }

        return $instance;
    }

    /**
     * @throws InvalidStep
     */
    public static function type(string $type, array $data = []): StepContract
    {
        $stepClass = self::types()->resolveClassFromType($type);
        if (!$stepClass) {
            throw new InvalidStep("Invalid step type '$type'");
        }

        $step = new $stepClass;
        if ($data) {
            $step->import($data);
        }

        return $step;
    }

    /**
     * @throws InvalidStep
     */
    public static function schema(string $schema, array $data = [], SchemaRegistry|string $registry = 'default'): StepContract
    {
        throw new InvalidStep("Invalid step schema '$schema'");
    }

    public static function types(): TypeFinder
    {
        // register core types
        if (!isset(self::$typeFinder)) {
            self::$typeFinder = (new TypeFinder)
                ->registerPrefix('', __NAMESPACE__.'\\Steps')
                ->registerPrefix('test:', __NAMESPACE__.'\\Testing\\Steps');
        }

        return self::$typeFinder;
    }
}
