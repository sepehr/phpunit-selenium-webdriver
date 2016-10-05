<?php

namespace Sepehr\PHPUnitSelenium\WebDriver;

/**
 * Improved WebDriverBy
 *
 * Facebook's WebDriverBy class does not utilize late static binding,
 * so we need to override all of its methods to make it extendable, see:
 * https://github.com/facebook/php-webdriver/issues/285
 */
class WebDriverBy extends \Facebook\WebDriver\WebDriverBy
{

    /**
     * Creates a WebDriverBy instance.
     *
     * @param string $mechanism
     * @param string $value
     *
     * @return static
     */
    public static function create($mechanism = null, $value = null)
    {
        return new static($mechanism, $value);
    }

    /**
     * Locates elements whose class name contains the search value; compound class
     * names are not permitted.
     *
     * @param string $className
     *
     * @return WebDriverBy
     */
    public static function className($className)
    {
        return new static('class name', $className);
    }

    /**
     * Locates elements matching a CSS selector.
     *
     * @param string $selector
     *
     * @return WebDriverBy
     */
    public static function cssSelector($selector)
    {
        return new static('css selector', $selector);
    }

    /**
     * Locates elements whose ID attribute matches the search value.
     *
     * @param string $id
     *
     * @return WebDriverBy
     */
    public static function id($id)
    {
        return new static('id', $id);
    }

    /**
     * Locates elements whose name attribute matches the search value.
     *
     * @param string $name
     *
     * @return WebDriverBy
     */
    public static function name($name)
    {
        return new static('name', $name);
    }

    /**
     * Locates anchor elements whose visible text matches the search value.
     *
     * @param string $linkText
     *
     * @return WebDriverBy
     */
    public static function linkText($linkText)
    {
        return new static('link text', $linkText);
    }

    /**
     * Locates anchor elements whose visible text partially matches the search
     * value.
     *
     * @param string $partialLinkText
     *
     * @return WebDriverBy
     */
    public static function partialLinkText($partialLinkText)
    {
        return new static('partial link text', $partialLinkText);
    }

    /**
     * Locates elements whose tag name matches the search value.
     *
     * @param string $tagName
     *
     * @return WebDriverBy
     */
    public static function tagName($tagName)
    {
        return new static('tag name', $tagName);
    }

    /**
     * Locates elements matching an XPath expression.
     *
     * @param string $xpath
     *
     * @return WebDriverBy
     */
    public static function xpath($xpath)
    {
        return new static('xpath', $xpath);
    }
}
