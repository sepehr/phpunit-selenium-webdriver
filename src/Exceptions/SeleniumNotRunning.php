<?php

namespace Sepehr\PHPUnitSelenium\Exceptions;

class SeleniumNotRunning extends Exception
{

    /**
     * Constructor.
     *
     * @param string|null $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        $message = "Seems like that Selenium is not running. To run Selenium issue this command:\n" .
            "    java -Dweb.gecko.driver=/path/to/geckodriver" .
            "-Dwebdriver.chrome.driver=/path/to/chromedriver -jar /path/to/selenium-server-standalone-*.jar\n" .
            "Make sure to pass -jar argument as the last argument, or you will encounter " .
            "\"Unknown option\" exception in newer versions of Selenium.\n\n" . $message;

        parent::__construct($message, $code, $previous);
    }
}
