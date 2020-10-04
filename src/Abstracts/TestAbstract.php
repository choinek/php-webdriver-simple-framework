<?php

namespace Choinek\PhpWebDriverSimpleFramework\Abstracts;

use Choinek\PhpWebDriverSimpleFramework\Exceptions\Failure;

abstract class TestAbstract
{
    /** @var \Facebook\WebDriver\WebDriver $driver */
    public $driver;
    public $session = [];

    function __construct($driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param $text
     */
    function info($text)
    {
        echo '[INFO]' . $text . "\n";
    }

    /**
     * This method is test's initializator
     * @return mixed
     * @throws Failure
     */
    abstract function run();
}
