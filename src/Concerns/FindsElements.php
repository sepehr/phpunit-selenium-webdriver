<?php

namespace Sepehr\PHPUnitSelenium\Concerns;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\Exception\NoSuchElementException;

trait FindsElements
{
    /**
     * Tries to find an element by its text, partial text, name or selector.
     *
     * @param string $criteria Element text, partial text, name or selector.
     *
     * @return $this
     * @throws NoSuchElementException
     */
    public function find($criteria)
    {
        if ($criteria instanceof RemoteWebElement) {
            return $this->setElement($criteria);
        }

        try {
            $this->findByLinkText($criteria);
        } catch (NoSuchElementException $e) {
            try {
                $this->findByName($criteria);
            } catch (NoSuchElementException $e) {
                try {
                    $this->findBySelector($criteria);
                } catch (NoSuchElementException $e) {
                    try {
                        $this->findByLinkPartialText($criteria);
                    } catch (NoSuchElementException $e) {
                        throw new NoSuchElementException("Unable to find an element with link text, partial link text, name or selector: $criteria");
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Finds elements by a WebDriverBy instance.
     *
     * @param WebDriverBy $by
     *
     * @return $this
     */
    public function findBy(WebDriverBy $by)
    {
        return $this->setElement($this->webDriver->findElement($by));
    }

    /**
     * Find an element by its name attribute.
     *
     * @param string $name
     *
     * @return $this
     */
    public function findByName($name)
    {
        return $this->findBy(WebDriverBy::name($name));
    }

    /**
     * Find an element by its CSS selector.
     *
     * @param string $selector
     *
     * @return $this
     */
    public function findBySelector($selector)
    {
        return $this->findBy(WebDriverBy::cssSelector($selector));
    }

    /**
     * Find an element by its value.
     *
     * @param string $value Value to check for.
     * @param string $element Target element tag.
     * @param bool $strict Strict comparison or not.
     *
     * @return $this
     */
    public function findByValue($value, $element = '*', $strict = true)
    {
        $op = $strict ? '=' : '*=';

        return $this->findBySelector("{$element}[value$op'$value']");
    }

    /**
     * Find an element by its containing value.
     *
     * @param string $value Value to check for.
     * @param string $element Target element tag.
     *
     * @return $this
     */
    public function findByContainingValue($value, $element = '*')
    {
        return $this->findByValue($value, $element, false);
    }

    /**
     * Find an element by its text.
     *
     * @param string $text Text to check for.
     * @param string $element Target element tag.
     * @param bool $strict Strict comparison or not.
     *
     * @return $this
     */
    public function findByText($text, $element = '*', $strict = true)
    {
        $op = $strict ? "text()='$text'" : "contains(text(), '$text')";

        return $this->findByXpath("//{$element}[$op]");
    }

    /**
     * Find an element by its containing text.
     *
     * @param string $text Text to check for.
     * @param string $element Target element tag.
     *
     * @return $this
     */
    public function findByContainingText($text, $element = '*')
    {
        return $this->findByText($text, $element, false);
    }

    /**
     * Find an element by its text or value.
     *
     * @param string $criteria Text or value to check for.
     * @param string $element Target element tag.
     * @param bool $strict Strict comparison or not.
     *
     * @return $this
     */
    public function findByTextOrValue($criteria, $element = '*', $strict = true)
    {
        try {
            return $this->findByValue($criteria, $element, $strict);
        } catch (NoSuchElementException $e) {
            return $this->findByText($criteria, $element, $strict);
        }
    }

    /**
     * Find an element by its containing text or value.
     *
     * @param string $criteria Text or value to check for.
     * @param string $element Target element tag.
     *
     * @return $this
     */
    public function findByContainingTextOrValue($criteria, $element = '*')
    {
        return $this->findByTextOrValue($criteria, $element, false);
    }

    /**
     * Find an element by its link text.
     *
     * @param string $text
     *
     * @return $this
     */
    public function findByLinkText($text)
    {
        return $this->findBy(WebDriverBy::linkText($text));
    }

    /**
     * Find an element by its partial link text.
     *
     * @param string $partialText
     *
     * @return $this
     */
    public function findByLinkPartialText($partialText)
    {
        return $this->findBy(WebDriverBy::cssSelector($partialText));
    }

    /**
     * Find an element by its XPath.
     *
     * @param $xpath
     *
     * @return $this
     */
    public function findByXpath($xpath)
    {
        return $this->findBy(WebDriverBy::xpath($xpath));
    }

    /**
     * Find elements by tag name.
     *
     * @param $tag
     *
     * @return $this
     */
    public function findByTag($tag)
    {
        return $this->findBy(WebDriverBy::tagName($tag));
    }
}
