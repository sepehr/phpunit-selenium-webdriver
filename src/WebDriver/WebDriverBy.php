<?php

namespace Sepehr\PHPUnitSelenium\WebDriver;

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
}
