<?php
/**
 * User: ardian
 */

namespace Choinek\PhpWebDriverSimpleFramework\Functions;

use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverElement;

/**
 * Class Elements
 */
class Elements {

    /**
     * @param WebDriverElement[] $elements
     * @return WebDriverElement
     */
    static function getRandomElement($elements)
    {
        $randomNumber = rand(0, count($elements) - 1);
        return $elements[$randomNumber];
    }

    /**
     * @todo everything do not use it
     * @param $elements
     * @param $function
     * @param int $maxCount
     */
    static function executeRandomElements($elements, $function, $maxCount = 3)
    {
//        for ($i = 0; $i < self::MAX_ADD_TO_CART_IN_CATEGORY; $i++) {
//
//            $this->addToCart($items[$randomNumber]);
//            $this->handleExtendedWarranty();
//        }

    }

    /**
     * Go to element (scroll window)
     * @param $element
     * @param RemoteWebDriver $driver
     * @throws \Exception
     */
    static function goTo($element, RemoteWebDriver $driver)
    {
        $action = new WebDriverActions($driver);

        self::waitUntilDomReadyState($driver);

        $driver->executeScript('window.scrollBy(0,1000);');

        $driver->executeScript('window.scrollBy(0,document.body.scrollHeight);');

        $action->moveToElement($element)->perform();
        $driver->executeScript('window.scrollBy(0,100);');
    }

    /**
     * @param RemoteWebDriver $driver
     * @return void
     * @throws \Exception
     */
    static function waitUntilDomReadyState(RemoteWebDriver $driver): void
    {
        $driver->wait()->until(function ($driver) {
            return $driver->executeScript('return document.readyState') === 'complete';
        });
    }
}
