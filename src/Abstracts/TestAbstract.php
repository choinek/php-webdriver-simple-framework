<?php

namespace Choinek\PhpWebDriverSimpleFramework\Abstracts;

use Choinek\PhpWebDriverSimpleFramework\Exceptions\Failure;
use Choinek\PhpWebDriverSimpleFramework\Helpers\Registry;
use Facebook\WebDriver\Remote\RemoteWebDriver;

abstract class TestAbstract
{
    /**
     * @todo move it to another class like error container
     */
    public const PRIORITY_LOW = 1;
    public const PRIORITY_MEDIUM = 2;
    public const PRIORITY_HIGH = 3;
    public const PRIORITY_CRITICAL = 4;

    public static $errorsLabels = [
        self::PRIORITY_LOW => 'Low',
        self::PRIORITY_MEDIUM => 'Medium',
        self::PRIORITY_HIGH => 'High',
        self::PRIORITY_CRITICAL => 'Critical',
    ];

    /**
     * @todo everything here should be passed as service
     */
    public static $errors;
    public static $successes;

    /** @var RemoteWebDriver $driver */
    public $driver;
    public $session = [];

    /**
     * @var array
     */
    public static $helpers = [];

    public function __construct(RemoteWebDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * In some environments visible information.
     * @param $text
     */
    public function debug($text): void
    {
        echo PHP_EOL . '[INFO]' . $text;
    }

    /**
     * Visible information.
     * @param $text
     */
    public function info($text): void
    {
        echo PHP_EOL . '[INFO]' . $text;
    }

    /**
     * @param $text
     */
    public function success($text): void
    {
        echo PHP_EOL . '[OK]' . $text;
        self::$successes++;
    }

    /**
     * This should be used, when an error occurred but further tests can be continued.
     * @param $text
     * @param $priority
     */
    public function error($text, $priority = self::PRIORITY_LOW): void
    {
        echo PHP_EOL . '[ERROR]' . $text;
        self::$errors[$priority] = !isset(self::$errors[$priority]) ? 1 : self::$errors[$priority] + 1;
    }

    /**
     * This should be used when critical error occurred and further testing should be stopped.
     * @param $text
     * @throws Failure
     */
    public function failure($text): void
    {
        echo PHP_EOL . '[FAILURE]' . $text;
        throw new Failure($text);
    }

    /**
     * @param $name
     * @param bool $singleton
     * @return mixed
     * @todo helpers should be implemented as service per test, not as static
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
     * @todo helpers should be implemented as service per test, not as static
     */
    public static function resetHelpers(): void
    {
        self::$helpers = [];
    }

    /**
     * This method is test's initializator
     * @return mixed
     * @throws Failure
     */
    abstract public function run();
}
