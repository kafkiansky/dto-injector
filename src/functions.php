<?php

if (!function_exists('toCamel')) {
    /**
     * @param string $it
     *
     * @return string
     */
    function toCamel(string $it): string
    {
        /** @var string $camelCased */
        $camelCased = preg_replace_callback(
            '/_(.?)/',
            static function (array $matches): string {
                return ucfirst((string) $matches[1]);
            },
            $it
        );

        return lcfirst($camelCased);
    }
}

if (!function_exists('traverseRecursive')) {
    /**
     * @psalm-param array<array-key, mixed> $data
     * @param array $data
     *
     * @return RecursiveIteratorIterator
     */
    function traverseRecursive(array $data): RecursiveIteratorIterator
    {
        return new RecursiveIteratorIterator(new RecursiveArrayIterator($data));
    }
}

if (!function_exists('propertyAccessible')) {
    /**
     * @param object|string $class
     * @param string $propertyName
     *
     * @return bool
     */
    function propertyAccessible(object|string $class, string $propertyName): bool
    {
        if (\is_object($class)) {
            $class = get_class($class);
        }

        $classVariables = array_flip(array_keys(get_class_vars($class)));

        return property_exists($class, $propertyName) && isset($classVariables[$propertyName]);
    }
}
