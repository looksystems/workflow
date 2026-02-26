<?php

namespace Look\Workflows\Core\Support;

class Str
{
    public static function kebab(string $value): string
    {
        return self::snake($value, '-');
    }

    public static function snake(string $value, string $delimiter = '_'): string
    {
        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));

            $value = strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
        }

        return $value;
    }

    public static function camel(string $value): string
    {
        return lcfirst(self::studly($value));
    }

    public static function studly(string $value): string
    {
        $words = explode(' ', str_replace(['-', '_'], ' ', $value));
        $studlyWords = array_map(fn ($word) => ucfirst($word), $words);

        return implode($studlyWords);
    }

    public static function type(object|string $class): string
    {
        return self::camel(self::classShort($class));
    }

    public static function classShort(object|string $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }
}
