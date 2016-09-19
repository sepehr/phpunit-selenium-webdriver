<?php

// TODOs:
// - Automagically run selenium
// - Extract to traits: InteractsWithPageElements, InteractWithForms, InteractWithInputs, InteractsWithWhateverTheFuck
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
// - Format docblocks
// - Log last response into file
// - DesiredCapabilities
// - Unified getter naming
// - Unit tests
// - assertElementExists(), seeElement()
// - assertElementCount()
// - Find child elements
// - assertHasChild(), seeChildElement()
// - Support for multiple elements ($this->element)
// - Better error messages
// - Public API to be protected, otherwise private
// - wait(), waitForElement()
// - updateUrl() wait issue
// - submitForm()
// - Touch events
// - Add example tests
// - Cookie assertions

namespace Sepehr\PHPUnitSelenium;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverPlatform;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\WebDriverCurlException;

abstract class SeleniumTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * Instance of RemoteWebDriver.
     *
     * @var \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected $webDriver;

    /**
     * Browser name.
     *
     * @var string
     */
    protected $browser = 'firefox';

    /**
     * Current URL.
     *
     * @var string
     */
    protected $currentUrl;

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
     * Current element.
     *
     * @var RemoteWebElement
     */
    protected $element;

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
            try {
                $this->webDriver = RemoteWebDriver::create(
                    $this->host,
                    $this->desiredCapabilities()
                );
            } catch (WebDriverCurlException $e) {
                throw new WebDriverCurlException(
                    $this->seleniumNotRunningMessage($e->getMessage())
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

    /**
     * Returns current URL.
     *
     * @return string
     */
    protected function url()
    {
        return $this->currentUrl;
    }

    /**
     * Set current URL.
     *
     * @param string $url URL to be set as current URL.
     *
     * @return $this
     */
    protected function setUrl($url)
    {
        $this->currentUrl = $this->normalizeUrl($url);

        return $this;
    }

    /**
     * Update current URL.
     *
     * @return $this
     */
    protected function updateUrl()
    {
        // NOTE:
        // Note that webdriver's current URL does not get changed immediately
        // after a click or submit action. So we need to wait a few seconds
        // for the URL to be updated. Any better ideas?!
        $this->wait();

        $this->setUrl($this->webDriver->getCurrentURL());

        return $this;
    }

    /**
     * Set current element;
     *
     * @param RemoteWebElement $element
     *
     * @return $this
     */
    protected function setElement(RemoteWebElement $element)
    {
        $this->element = $element;

        return $this;
    }

    /**
     * Execute a command on current element.
     *
     * @param string|array $action Action(s) to be executed on element.
     * @param null|string $element Element to execute the action on.
     * @param bool $changesUrl Whether the action might change the URL or not.
     *
     * @return $this
     * @throws \Exception
     */
    protected function elementAction($action, $element = null, $changesUrl = false)
    {
        $element and $this->find($element);

        if (! $this->element) {
            throw new \Exception('No element is targeted to execute the action(s) on.');
        }

        is_array($action) or $action = [$action => []];

        foreach ($action as $method => $args) {
            if (! method_exists($this->element, $method)) {
                throw new \Exception("Invalid element action: $method");
            }

            is_array($args) or $args = [$args];

            call_user_func_array([$this->element, $method], $args);
        }

        $changesUrl and $this->updateUrl();

        return $this;
    }

    /**
     * Returns a proper DesiredCapability instance for webdriver session.
     *
     * @return DesiredCapabilities
     */
    protected function desiredCapabilities()
    {
        if (method_exists(DesiredCapabilities::class, $this->browser)) {
            return DesiredCapabilities::{$this->browser}();
        }

        return new DesiredCapabilities([
            WebDriverCapabilityType::BROWSER_NAME => $this->browser,
            WebDriverCapabilityType::PLATFORM => WebDriverPlatform::ANY
        ]);
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
    public function visit($path = '/')
    {
        $this->createSession();

        $this->setUrl($path);

        $this->webDriver->get($this->url());

        $this->updateUrl();

        return $this;
    }

    /**
     * Wait :)
     *
     * @param int $seconds Number of seconds to wait.
     *
     * @return $this
     */
    public function wait($seconds = 3)
    {
        sleep($seconds);

        return $this;
    }

    /**
     * Types into an element.
     *
     * @param string $text Text to type into the element.
     * @param string|null $element Text, name or selector of the element.
     *
     * @return $this
     */
    public function type($text, $element = null)
    {
        return $this->elementAction(['sendKeys' => $text], $element, true);
    }

    /**
     * Hits a single key, hardly.
     *
     * @param string $key Key to hit.
     *
     * @return $this
     * @throws \Exception
     * @see WebDriverKeys
     */
    public function hit($key)
    {
        $const = WebDriverKeys::class . '::' . strtoupper($key);

        if (! defined($const)) {
            throw new \Exception("Invalid key: $key");
        }

        return $this->type(constant($const));
    }

    /**
     * Alias for hit().
     *
     * @param string $key Key to hit.
     *
     * @return $this
     */
    public function press($key)
    {
        return $this->hit($key);
    }

    /**
     * Hits enter.
     *
     * @return $this
     */
    public function enter()
    {
        return $this->hit('enter');
    }

    /**
     * Hits escape.
     *
     * @return $this
     */
    public function esc()
    {
        return $this->hit('escape');
    }

    /**
     * Hits tab.
     *
     * @return $this
     */
    public function tab()
    {
        return $this->hit('tab');
    }

    /**
     * Hits backspace.
     *
     * @return $this
     */
    public function backspace()
    {
        return $this->hit('backspace');
    }

    /**
     * Click on an element.
     *
     * @param string|null $element Text, name or selector of the element.
     *
     * @return $this
     */
    public function click($element = null)
    {
        return $this->elementAction('click', $element, true);
    }

    /**
     * Alias for click() with mandatory element.
     *
     * @param string|null $element Text, name or selector of the element.
     *
     * @return $this
     */
    public function clickOn($element)
    {
        return $this->click($element);
    }

    /**
     * Submit an element.
     *
     * @param string|null $element Text, name or selector of the element.
     *
     * @return $this
     */
    public function submit($element = null)
    {
        return $this->elementAction('clear', $element, true);
    }

    /**
     * Clear an element, if a textarea or an input.
     *
     * @param string|null $element Text, name or selector of the element.
     *
     * @return $this
     */
    public function clear($element = null)
    {
        return $this->elementAction('clear', $element);
    }

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
            $this->findByText($criteria);
        } catch (NoSuchElementException $e) {
            try {
                $this->findByName($criteria);
            } catch (NoSuchElementException $e) {
                try {
                    $this->findBySelector($criteria);
                } catch (NoSuchElementException $e) {
                    try {
                        $this->findByPartialText($criteria);
                    } catch (NoSuchElementException $e) {
                        throw new NoSuchElementException(
                            "Unable to find an element with link text, partial link text, name or selector: $criteria"
                        );
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
     * Find an element by its link text.
     *
     * @param string $text
     *
     * @return $this
     */
    public function findByText($text)
    {
        return $this->findBy(WebDriverBy::linkText($text));
    }

    /**
     * Find an element by its value.
     *
     * @param string $value
     *
     * @return $this
     */
    public function findByValue($value)
    {
        // @TODO: Implement.
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
     * Find an element by its partial link text.
     *
     * @param string $partialText
     *
     * @return $this
     */
    public function findByPartialText($partialText)
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

    /**
     * Set browser name.
     *
     * Can be "firefox", "chrome", "phantomjs" or any other driver
     * available to Selenium executable.
     *
     * @param string $browser Browser name.
     *
     * @return $this
     * @throws \Exception
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
    // Assertions
    // ----------------------------------------------------------------------------

    /**
     * Assert that the page URL matches the given URL.
     *
     * @param string $url URL to check.
     * @param string $message PHPUnit error message.
     * @param bool $negate Negate the check?
     *
     * @return $this
     */
    public function assertPageIs($url, $message = '', $negate = false)
    {
        $url = $this->normalizeUrl($url);
        $method = $negate ? 'assertNotEquals' : 'assertEquals';

        $this->$method($url, $this->url(), $message);

        return $this;
    }

    // ----------------------------------------------------------------------------
    // Laravelish Assertions
    // ----------------------------------------------------------------------------

    /**
     * Assert that the page URL matches the given URL.
     *
     * @param string $url Url to check.
     *
     * @return $this
     */
    public function seePageIs($url)
    {
        return $this->assertPageIs($url, "Failed asserting that the current page is: $url");
    }

    /**
     * Assert that the page URL does not matche the given URL.
     *
     * @param string $url Url to check.
     *
     * @return $this
     */
    public function dontSeePageIs($url)
    {
        return $this->assertPageIs($url, "Failed asserting that the current page is NOT: $url", true);
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

        if (strpos($path, 'http') !== 0) {
            $path = rtrim($this->baseUrl, '/') . '/' . $path;
        }

        return rtrim($path, '/');
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
     * @param string $original Original error message.
     *
     * @return string
     */
    private function seleniumNotRunningMessage($original = '')
    {
        return "Seems like that Selenium is not running. To run Selenium issue this command:\n" .
               "    java -Dweb.gecko.driver=/path/to/geckodriver" .
               "-Dwebdriver.chrome.driver=/path/to/chromedriver -jar /path/to/selenium-server-standalone-*.jar\n" .
               "Make sure to pass -jar argument as the last argument, or you will encounter " .
               "\"Unknown option\" exception in newer versions of Selenium.\n\n" . $original;
    }
}
