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

    /**
     * Get data from registry
     *
     * @param string $key
     * @param string $namespace optional
     * @return mixed|null
     */
    public static function getData(string $key, string $namespace = self::DEFAULT_NAMESPACE)
    {
        return self::$data[$namespace][$key] ?? null;
    }

    /**
     * @param string $name
     * @param $value
     * @param string $namespace
     */
    public static function setData(string $name, $value, string $namespace = self::DEFAULT_NAMESPACE): void
    {
        self::$data[$namespace][$name] = $value;
    }

    /**
     * Reset registry by namespace
     * @param string $namespace
     */
    public static function reset(string $namespace = self::DEFAULT_NAMESPACE): void
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
