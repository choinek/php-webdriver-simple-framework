<?php

namespace Choinek\PhpWebDriverSimpleFramework\Helpers;

/**
 * Class Elements
 */
class Registry
{
    public const DEFAULT_NAMESPACE = '__DEFAULT_NAMESPACE_';
    
    /**
     * @var array
     */
    public static $data = [];

    public static function getData($name, $namespace = self::DEFAULT_NAMESPACE): bool
    {
        return self::$data[$namespace][$name] ?? false;
    }

    /**
     * @param $name
     * @param $value
     * @param string $namespace
     */
    public static function setData($name, $value, $namespace = self::DEFAULT_NAMESPACE): void
    {
        self::$data[$namespace][$name] = $value;
    }

    /**
     * Reset registry by namespace
     * @param string $namespace
     */
    public static function reset($namespace = self::DEFAULT_NAMESPACE): void
    {
        if (self::$data[$namespace]) {
            unset(self::$data[$namespace]);
        }
    }

    /**
     * Reset all data in registry
     */
    public static function resetAll(): void
    {
        self::$data = [];
    }

}
