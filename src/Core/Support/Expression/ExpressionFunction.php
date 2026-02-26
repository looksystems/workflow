<?php

namespace Look\Workflows\Core\Support\Expression;

use Closure;

class ExpressionFunction
{
    public static function value(): Closure
    {
        return function ($args, ...$values) {
            $value = data_get($args, ...$values);
            return is_numeric($value) ? $value + 0 : $value;
        };
    }

    public static function count(): Closure
    {
        return function ($args, ...$values) {
            return count($values[0]);
        };
    }

    public static function min(): Closure
    {
        return function ($args, ...$values) {
            if (
                count($values) === 2
                && is_iterable($values[0])
                && is_string($values[1])
            ) {
                return collect($values[0])->min($values[1]);
            }

            return collect($values)->flatten()->min();
        };
    }

    public static function max(): Closure
    {
        return function ($args, ...$values) {
            if (
                count($values) === 2
                && is_iterable($values[0])
                && is_string($values[1])
            ) {
                return collect($values[0])->max($values[1]);
            }

            return collect($values)->flatten()->max();
        };
    }

    public static function average(): Closure
    {
        return function ($args, ...$values) {
            if (
                count($values) === 2
                && is_iterable($values[0])
                && is_string($values[1])
            ) {
                return collect($values[0])->average($values[1]);
            }

            return collect($values)->flatten()->average();
        };
    }

    public static function median(): Closure
    {
        return function ($args, ...$values) {
            if (
                count($values) === 2
                && is_iterable($values[0])
                && is_string($values[1])
            ) {
                return collect($values[0])->median($values[1]);
            }

            return collect($values)->flatten()->median();
        };
    }

    public static function sum(): Closure
    {
        return function ($args, ...$values) {
            if (
                count($values) === 2
                && is_iterable($values[0])
                && is_string($values[1])
            ) {
                return collect($values[0])->sum($values[1]);
            }

            return collect($values)->flatten()->sum();
        };
    }

    public static function query(): Closure
    {
        return function ($args, ...$values) {
            if (
                count($values) === 2
                && is_iterable($values[0])
                && is_string($values[1])
            ) {
                return JmesPath::search($values[1], $values[0], throwIfSyntaxError: false);
            }

            return null;
        };
    }

    public static function filter(): Closure
    {
        return function ($args, ...$values) {
            if (
                count($values) === 2
                && is_iterable($values[0])
                && is_string($values[1])
            ) {
                $key = $values[1];
                return collect($values[0])
                    ->filter(function ($item) use ($key) {
                        return $item[$key];
                    })
                    ->toArray();
            }

            return [];
        };
    }
}
