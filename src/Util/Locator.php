<?php

namespace Sepehr\PHPUnitSelenium\Util;

use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\CssSelector\Exception\ParseException;

class Locator
{

    /**
     * Validates a CSS selector.
     *
     * @param string $selector
     *
     * @return bool
     */
    public static function isSelector($selector)
    {
        try {
            return !! (new CssSelectorConverter())->toXPath($selector);
        } catch (ParseException $e) {
            return false;
        }
    }

    /**
     * Validates XPath.
     *
     * @param string $xpath
     *
     * @return bool
     */
    public static function isXpath($xpath)
    {
        $domDoc   = new \DOMDocument('1.0', 'UTF-8');
        $domXpath = new \DOMXPath($domDoc);

        return @$domXpath->evaluate($xpath, $domDoc) !== false;
    }
}
