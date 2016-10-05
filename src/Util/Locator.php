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

        // Conflicts with HHVM, see:
        // https://github.com/facebook/hhvm/issues/5790
        libxml_use_internal_errors(true);

        return @$domXpath->evaluate($xpath, $doc) !== false;
    }

    /**
     * Checks whether it's a element locator or not.
     *
     * @param mixed $wtf Thing to check.
     *
     * @return bool
     */
    public static function isLocator($wtf)
    {
        return is_string($wtf);
    }
}
