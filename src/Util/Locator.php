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
        $domXpath = new \DOMXPath($doc = new \DOMDocument('1.0', 'UTF-8'));

        return @$domXpath->evaluate($xpath, $doc) !== false;
    }
}
