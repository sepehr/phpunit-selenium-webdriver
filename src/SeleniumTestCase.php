<?php

namespace Sepehr\PHPUnitSelenium;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverPlatform;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Exception\WebDriverCurlException;
use Facebook\WebDriver\Exception\NoSuchElementException as NoSuchElement;

abstract class SeleniumTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * Instance of RemoteWebDriver.
     *
     * @var \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected $driver;

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
    protected function tearDownDriver()
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
        if ($force or ! $this->driverLoaded()) {
            try {
                $this->driver = RemoteWebDriver::create(
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
        if ($this->driver instanceof RemoteWebDriver) {
            $this->driver->quit();
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
     * Return webdriver's current URL.
     *
     * @return string
     */
    protected function driverUrl()
    {
        return $this->driver->getCurrentURL();
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

        $this->setUrl($this->driverUrl());

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

    /**
     * Set current element.
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
     * Returns current element.
     *
     * @return RemoteWebElement
     */
    protected function getElement()
    {
        return $this->element;
    }

    /**
     * Returns current page's title.
     *
     * @return string
     */
    protected function getPageTitle()
    {
        return $this->driver->getTitle();
    }

    /**
     * Returns current page's source.
     *
     * @return string
     */
    protected function getPageSource()
    {
        return $this->driver->getPageSource();
    }

    /**
     * Execute a command on the current element.
     *
     * Proxies actions to current RemoteWebElement object with some initial housekeepings.
     *
     * @param string|array $action Action(s) to be executed on element.
     * @param null|string $element Element criteria to execute the action on.
     * @param bool $changesUrl Whether the action might change the URL or not.
     *
     * @return $this
     * @throws \Exception
     */
    protected function elementAction($action, $element = null, $changesUrl = false)
    {
        $element and $this->find($element);

        if ( ! $this->element) {
            throw new \Exception('No element is targeted to execute the action(s) on.');
        }

        is_array($action) or $action = [$action => []];

        foreach ($action as $method => $args) {
            if ( ! method_exists($this->element, $method)) {
                throw new \Exception("Invalid element action: $method");
            }

            is_array($args) or $args = [$args];

            call_user_func_array([$this->element, $method], $args);
        }

        $changesUrl and $this->updateUrl();

        return $this;
    }

    // ----------------------------------------------------------------------------
    // Public API
    // ----------------------------------------------------------------------------

    /**
     * Visit a URL.
     *
     * @param string $url URL to visit.
     *
     * @return $this
     */
    public function visit($url = '/')
    {
        $this->createSession();

        $this->setUrl($url);

        $this->driver->get($this->url());

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

    /**
     * Tries to find an element by examining CSS selector, name, value or text.
     *
     * Examination order:
     * 1. CSS selector
     * 2. Name
     * 3. Value
     * 4. Text
     *
     * NOTE:
     * This is an expensive method; Prefer to utilize explicit find
     * methods instead unless operating in "whadeva" mode!
     *
     * @param string|RemoteWebElement $criteria Element criteria.
     *
     * @return $this
     * @throws NoSuchElement
     * @see findBySelector(), findByName(), findByValue(), findByText()
     */
    public function find($criteria)
    {
        if ($this->isElement($criteria)) {
            return $this->setElement($criteria);
        }

        try {
            $this->findBySelector($criteria);
        } catch (NoSuchElement $e) {
            try {
                $this->findByName($criteria);
            } catch (NoSuchElement $e) {
                try {
                    $this->findByValue($criteria);
                } catch (NoSuchElement $e) {
                    try {
                        $this->findByText($criteria);
                    } catch (NoSuchElement $e) {
                        throw new NoSuchElement(
                            "Unable to find an element with CSS selector, name, value or text: $criteria"
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
        return $this->setElement($this->driver->findElement($by));
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
    public function findByPartialValue($value, $element = '*')
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
    public function findByPartialText($text, $element = '*')
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
        } catch (NoSuchElement $e) {
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
    public function findByPartialTextOrValue($criteria, $element = '*')
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
        return $this->findBy(WebDriverBy::partialLinkText($partialText));
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
     * Types into an element.
     *
     * @param string $text Text to type into the element.
     * @param string|null $element Element criteria.
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

        if ( ! defined($const)) {
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
     * Click on an element.
     *
     * @param string|null $element Element criteria.
     *
     * @return $this
     */
    public function click($element = null)
    {
        return $this->elementAction('click', $element, true);
    }

    /**
     * Alias for click().
     *
     * @param string|null $element Element criteria.
     *
     * @return $this
     */
    public function follow($element = null)
    {
        return $this->click($element);
    }

    /**
     * Alias for click() with mandatory element.
     *
     * @param string|null $element Element criteria.
     *
     * @return $this
     */
    public function clickOn($element)
    {
        return $this->click($element);
    }

    /**
     * Clear an element, if a textarea or an input.
     *
     * @param string|null $element Element criteria.
     *
     * @return $this
     */
    public function clear($element = null)
    {
        return $this->elementAction('clear', $element);
    }

    /**
     * Submit a form using one of its containing elements.
     *
     * If this current element is a form, or an element within a form, then this
     * will be submitted to the remote server.
     *
     * @param string|null $element Element criteria.
     *
     * @return $this
     */
    public function submit($element = null)
    {
        return $this->elementAction('submit', $element, true);
    }

    /**
     * Submit a form.
     *
     * @param string|null $form Form selector, name or its submit button text.
     * @param array $formData Array of name/value pairs as form data for submission.
     *
     * @return $this
     * @throws NoSuchElement
     */
    public function submitForm($form = null, $formData = [])
    {
        try {
            $form = $this->find($form);
        } catch (NoSuchElement $e) {
            throw new NoSuchElement("Could not find the form with selector, name or button text: $form");
        }

        foreach ($formData as $name => $value) {
            $this->findByName($name)
                 ->type($value);
        }

        // @TODO: Continue the implementation...
    }

    // ----------------------------------------------------------------------------
    // Assertions
    // ----------------------------------------------------------------------------

    /**
     * Assert that the page URL matches the given URL.
     *
     * @param string $url
     * @param string $message
     * @param bool $negate
     *
     * @return $this
     */
    public function assertPageIs($url, $message = '', $negate = false)
    {
        $url = $this->normalizeUrl($url);
        $method = $this->getAssertionMethod('assertEquals', $negate);

        $this->$method($url, $this->url(), $message);

        return $this;
    }

    /**
     * Assert that the page URL matches the given URL.
     *
     * @param string $url
     *
     * @return $this
     */
    public function seePageIs($url)
    {
        return $this->assertPageIs(
            $url,
            "Failed asserting that the current page is: $url"
        );
    }

    /**
     * Assert that the page URL does not match the given URL.
     *
     * @param string $url
     *
     * @return $this
     */
    public function dontSeePageIs($url)
    {
        return $this->assertPageIs(
            $url,
            "Failed asserting that the current page is NOT: $url",
            true
        );
    }

    /**
     * Assert that the page source contains the specified text.
     *
     * @param string $text
     * @param string $message
     * @param bool $negate
     *
     * @return $this
     */
    public function assertPageContains($text, $message = '', $negate = false)
    {
        $text = preg_quote($text, '/');
        $method = $this->getAssertionMethod('assertRegExp', $negate);

        $this->$method("/{$text}/i", $this->getPageSource(), $message);

        return $this;
    }

    /**
     * Asserts that the page source contains the specified text.
     *
     * @param string $text
     *
     * @return $this
     */
    public function see($text)
    {
        return $this->assertPageContains(
            $text,
            "Failed asserting that the current page source contains: $text"
        );
    }

    /**
     * Asserts that the page source does NOT contain the specified text.
     *
     * @param string $text
     *
     * @return $this
     */
    public function dontSee($text)
    {
        return $this->assertPageContains(
            $text,
            "Failed asserting that the current page source does NOT contain: $text",
            true
        );
    }

    /**
     * Assert that the page title matches the given string.
     *
     * @param string $title
     * @param string $message
     * @param bool $negate
     *
     * @return $this
     */
    public function assertTitleIs($title, $message = '', $negate = false)
    {
        $method = $this->getAssertionMethod('assertEquals', $negate);

        $this->$method($title, $this->getPageTitle(), $message);

        return $this;
    }

    /**
     * Assert that the page title matches the given string.
     *
     * @param string $title
     *
     * @return $this
     */
    public function seeTitle($title)
    {
        return $this->assertTitleIs(
            $title,
            "Failed asserting that the current page title is: $title"
        );
    }

    /**
     * Assert that the page title does not matche the given string.
     *
     * @param string $title
     *
     * @return $this
     */
    public function dontSeeTitle($title)
    {
        return $this->assertTitleIs(
            $title,
            "Failed asserting that the current page title is NOT: $title",
            true
        );
    }

    /**
     * Assert that the page title contains the given string.
     *
     * @param string $title
     * @param string $message
     * @param bool $negate
     *
     * @return $this
     */
    public function assertTitleContains($title, $message = '', $negate = false)
    {
        $method = $this->getAssertionMethod('assertContains', $negate);

        $this->$method($title, $this->getPageTitle(), $message);

        return $this;
    }

    /**
     * Assert that the page title contains the given string.
     *
     * @param string $title
     *
     * @return $this
     */
    public function seeTitleContains($title)
    {
        return $this->assertTitleContains(
            $title,
            "Failed asserting that the current page title contains: $title"
        );
    }

    /**
     * Assert that the page title does not contain the given string.
     *
     * @param string $title
     *
     * @return $this
     */
    public function dontSeeTitleContains($title)
    {
        return $this->assertTitleContains(
            $title,
            "Failed asserting that the current page title does NOT contain: $title",
            true
        );
    }

    /**
     * Asserts that an element exists on the current page.
     *
     * @param string $element
     * @param string $message
     * @param bool $negate
     */
    public function assertElementExists($element, $message = '', $negate = false)
    {
        //
    }

    public function seeElement($element)
    {
        //
    }

    public function dontSeeElement($element)
    {
        //
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
        // Consider file:/// URLs as normalized
        if (strpos($path, 'file:///') === 0) {
            return $path;
        }

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
    private function driverLoaded()
    {
        return $this->driver instanceof RemoteWebDriver;
    }

    /**
     * Checks whether it's a valid RemoteWebElement or not.
     *
     * @param mixed $godKnowsWhatTheFuck Thing to check.
     *
     * @return bool
     */
    private function isElement($godKnowsWhatTheFuck)
    {
        return $godKnowsWhatTheFuck instanceof RemoteWebElement;
    }

    /**
     * Returns proper PHPUnit assertion method name depending on $negate.
     *
     * @param string $method
     * @param bool $negate
     *
     * @return string
     */
    private function getAssertionMethod($method, $negate)
    {
        $negates = [
            'assertEquals'   => 'assertNotEquals',
            'assertRegExp'   => 'assertNotRegExp',
            'assertContains' => 'assertNotContains',
        ];

        return $negate ? $negates[$method] : $method;
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
