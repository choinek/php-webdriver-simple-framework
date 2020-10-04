<?php
/**
 * User: ardian
 */

namespace Choinek\PhpWebDriverSimpleFramework\Functions;

use Facebook\WebDriver\Interactions\WebDriverActions;
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
     * @param $driver
     */
    static function goTo($element, $driver)
    {
        $action = new WebDriverActions($driver);
        $action->moveToElement($element)->perform();
        $driver->executeScript('window.scrollBy(0,100);');
    }
}
