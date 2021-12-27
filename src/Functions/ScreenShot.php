<?php


namespace Choinek\PhpWebDriverSimpleFramework\Functions;

use Facebook\WebDriver\Remote\RemoteWebDriver;

/**
 * Class ScreenShot
 * @package Choinek\PhpWebDriverSimpleFramework\Functions
 */
class ScreenShot
{
    /**
     * Based on konkon1234's gist
     *
     * @param RemoteWebDriver $driver
     * @param string|null $filename capture save path
     * @throws \Exception
     */
    public static function full(RemoteWebDriver $driver, string $filename = null): void
    {
        if (!$filename) {
            $filename = time() . 'png';
        }

        if (!(strlen($filename) - strripos($filename, '.png') !== 4)) {
            $filename .= '.png';
        }

        $totalWidth = $driver->executeScript('return Math.max.apply(null, [document.body.clientWidth, document.body.scrollWidth, document.documentElement.scrollWidth, document.documentElement.clientWidth])');
        $totalHeight = $driver->executeScript('return Math.max.apply(null, [document.body.clientHeight, document.body.scrollHeight, document.documentElement.scrollHeight, document.documentElement.clientHeight])');

        $viewportWidth = $driver->executeScript('return document.documentElement.clientWidth');
        $viewportHeight = $driver->executeScript('return document.documentElement.clientHeight');

        $driver->executeScript('window.scrollTo(0, 0)');

        $fullCapture = imagecreatetruecolor($totalWidth, $totalHeight);

        $repeatX = ceil($totalWidth / $viewportWidth);
        $repeatY = ceil($totalHeight / $viewportHeight);

        for ($x = 0; $x < $repeatX; $x ++) {
            $xPos = $x * $viewportWidth;

            $beforeTop = -1;
            for ($y = 0; $y < $repeatY; $y++) {
                $yPos = $y * $viewportHeight;
                $driver->executeScript("window.scrollTo({$xPos}, {$yPos})");

                $scrollLeft = $driver->executeScript("return window.pageXOffset");
                $scrollTop = $driver->executeScript("return window.pageYOffset");
                if ($beforeTop === $scrollTop) {
                    break;
                }

                $tmpName = "{$filename}.tmp";
                $driver->takeScreenshot($tmpName);
                if (!file_exists($tmpName)) {
                    throw new \Exception('Could not save screenshot');
                }

                $tmpImage = imagecreatefrompng($tmpName);
                imagecopy($fullCapture, $tmpImage, $scrollLeft, $scrollTop, 0, 0, $viewportWidth, $viewportHeight);
                imagedestroy($tmpImage);
                unlink($tmpName);

                $beforeTop = $scrollTop;
            }
        }

        imagepng($fullCapture, $filename);
        imagedestroy($fullCapture);
    }
}
