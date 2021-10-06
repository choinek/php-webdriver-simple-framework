<?php

namespace Choinek\PhpWebDriverSimpleFramework\Abstracts;

use Choinek\PhpWebDriverSimpleFramework\Exceptions\Failure;
use Choinek\PhpWebDriverSimpleFramework\Helpers\Registry;
use Facebook\WebDriver\WebDriver;

abstract class TestAbstract
{
    /** @var WebDriver $driver */
    public $driver;
    public $session = [];

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param $text
     */
    public function info($text): void
    {
        echo '[INFO]' . $text . "\n";
    }

    /**
     * @param $name
     * @param bool $singleton
     * @return mixed
     */
    public function helper($name, $singleton = true)
    {
        $name = str_replace('/', '\\', $name);

        $className = Registry::getData(
            Registry::CFG_BASE_NAMESPACE,
            Registry::CONFIG_NAMESPACE
            ) . '\\Helpers\\'
            . $name;

        if (!$singleton) {
            return new $className($this->driver);
        }

        if (!isset(self::$helpers[$name])) {
            self::$helpers[$name] = new $className($this->driver);
        }

        return self::$helpers[$name];
    }

    /**
     * This method is test's initializator
     * @return mixed
     * @throws Failure
     */
    abstract public function run();
}
