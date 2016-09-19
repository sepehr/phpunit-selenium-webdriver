<?php

// TODOs:
// - Automagically run selenium
// - Extract to traits
// - Adhere to some fuckin Contract
// - Parse an optional selenium.json config
// - Develop Laravel fluent testing API (see InteractsWithPages trait)
// - PHPUnit assertBullshit* testing API
// - Review Codeception acceptance testing API
// - Method aliases (click, touch, andClick, etc.)
// - Class alias (PHPUnit_Extension_SeleniumWebDriverTestCase)
// - Typehint the fuck out of it
// - User-friendly error messages
// - Detect if Selenium is running
// - Detect if Selenium browser driver is present
// - Exceptions

namespace Sepehr\PHPUnitSelenium;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Exception\WebDriverCurlException;

abstract class SeleniumTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * Instance of RemoteWebDriver.
     *
     * @var \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    public $webDriver;

    /**
     * Browser name.
     *
     * @var string
     */
    protected $browser = 'phantomjs';

    /**
     * Base URL for all requests.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost:8888/';

    /**
     * Selenium host.
     *
     * @var string
     */
    protected $host = 'http://localhost:4444/wd/hub';

    /**
     * Current URL.
     *
     * @var string
     */
    protected $currentUrl;

    /**
     * Destroys webdriver session after the test.
     *
     * @return $this
     * @after
     */
    protected function tearDownWebDriver()
    {
        return $this->destroySession();
    }

    // ----------------------------------------------------------------------------
    // Protected API
    // ----------------------------------------------------------------------------

    /**
     * Initiates webdriver session.
     *
     * @param bool $force Whether to force a new session or to user the old one.
     *
     * @return $this
     * @throws WebDriverCurlException
     */
    protected function createSession($force = false)
    {
        if ($force or ! $this->webDriverLoaded()) {
            $capabilities = [
                WebDriverCapabilityType::BROWSER_NAME => $this->browser,
            ];

            try {
                $this->webDriver = RemoteWebDriver::create($this->host, $capabilities);
            } catch (WebDriverCurlException $e) {
                throw new WebDriverCurlException(
                    $this->notRunningErrorMessage($e->getMessage())
                );
            }
        }

        return $this;
    }

    /**
     * Destroys webdriver session, if available.
     *
     * @return $this
     */
    protected function destroySession()
    {
        if ($this->webDriver instanceof RemoteWebDriver) {
            $this->webDriver->quit();
        }

        return $this;
    }

    // ----------------------------------------------------------------------------
    // Public API
    // ----------------------------------------------------------------------------

    /**
     * Visit a URL.
     *
     * @param string $path URL to visit.
     *
     * @return $this
     */
    public function visit($path)
    {
        $this->createSession();

        $this->currentUrl = $this->normalizeUrl($path);

        $this->webDriver->get($this->currentUrl);

        return $this;
    }

    /**
     * Set browser name.
     *
     * Can be "firefox", "chrome", "phantomjs" or any other driver
     * available to Selenium executable.
     *
     * @param string $browser Browser name.
     *
     * @return $this
     */
    public function browser($browser)
    {
        $this->browser = $browser;

        return $this;
    }

    /**
     * Set base URL for all requests.
     *
     * @param string $url Base URL to be set.
     *
     * @return $this
     */
    public function baseUrl($url)
    {
        $this->baseUrl = $url;

        return $this;
    }

    // ----------------------------------------------------------------------------
    // Private Helpers
    // ----------------------------------------------------------------------------

    /**
     * Preps relative URL.
     *
     * @param string $path Path to be prepped.
     *
     * @return string
     */
    private function normalizeUrl($path)
    {
        if ($path[0] === '/') {
            $path = substr($path, 1);
        }

        if (strpos($path, 'http') === false) {
            $path = "{$this->baseUrl}/$path";
        }

        return trim($path, '/');
    }

    /**
     * Check if webdriver is loaded.
     *
     * @return bool
     */
    private function webDriverLoaded()
    {
        return $this->webDriver instanceof RemoteWebDriver;
    }

    /**
     * Returns a comprehensive "Selenium is not running" error message.
     *
     * @param string $error Original error message.
     *
     * @return string
     */
    private function notRunningErrorMessage($error = '')
    {
        return "Seems like that Selenium is not running. To run Selenium issue this command:\n" .
               "    java -Dweb.gecko.driver=/path/to/geckodriver" .
               "-Dwebdriver.chrome.driver=/path/to/chromedriver -jar /path/to/selenium-server-standalone-*.jar\n" .
               "Make sure to pass -jar argument as the last argument, or you will encounter " .
               "\"Unknown option\" exception in newer versions of Selenium.\n\n" . $error;
    }
}
