<?php

namespace Choinek\PhpWebDriverSimpleFramework\Functions;

/**
 * @class Unification
 * @author Adrian Chojnicki <adrian@chojnicki.pl>
 */
class Unification
{

    /**
     * Unificate price, convert everything to format XX.YY (decimal range is optional)
     * @param string $value
     * @param int $decimalRange
     * @return string
     */
    static function price($value, $decimalRange = 2)
    {
        $value = str_replace(',', '.', $value);
        $value = (string)preg_replace('/\D\./', '', $value);
        $value = round($value, $decimalRange);

        return $value;
    }
}
