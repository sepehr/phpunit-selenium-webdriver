<?php

namespace Sepehr\PHPUnitSelenium;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverPlatform;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Exception\WebDriverCurlException;
use Facebook\WebDriver\Exception\NoSuchElementException;

use Sepehr\PHPUnitSelenium\Exceptions\NoSuchElement;
use Sepehr\PHPUnitSelenium\Exceptions\InvalidArgument;
use Sepehr\PHPUnitSelenium\Exceptions\SeleniumNotRunning;

abstract class SeleniumTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * Instance of RemoteWebDriver.
     *
     * @var RemoteWebDriver
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
     * Webdriver connection timeout.
     *
     * @var int
     */
    protected $connectionTimeout = 30000;

    /**
     * Webdriver cURL request timeout.
     *
     * @var int
     */
    protected $requestTimeout = 90000;

    /**
     * Webdriver proxy host.
     *
     * @var string|null
     */
    protected $httpProxy = null;

    /**
     * Webdriver proxy port.
     *
     * @var int|null
     */
    protected $httpProxyPort = null;

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
     * @throws SeleniumNotRunning
     */
    protected function createSession($force = false)
    {
        if ($force or ! $this->driverLoaded()) {
            try {
                $this->driver = RemoteWebDriver::create(
                    $this->host,
                    $this->desiredCapabilities(),
                    $this->connectionTimeout,
                    $this->requestTimeout,
                    $this->httpProxy,
                    $this->httpProxyPort
                );
            } catch (WebDriverCurlException $e) {
                throw new SeleniumNotRunning($e->getMessage());
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
     * Returns webdriver instance.
     *
     * @return RemoteWebDriver
     */
    protected function webDriver()
    {
        return $this->driver;
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
     * Returns a WebDriverBy instance.
     *
     * @param string $mechanism Valid mechanism.
     * @param string $value
     *
     * @return WebDriverBy
     * @throws InvalidArgument
     * @see WebDriverBy
     */
    protected function getWebDriverByInstance($mechanism, $value = '')
    {
        if (! method_exists(WebDriverBy::class, $mechanism)) {
            throw new InvalidArgument("Invalid WebDriverBy mechanism: $mechanism");
        }

        return WebDriverBy::$mechanism($value);
    }

    /**
     * Returns the full class name for a valid element.
     *
     * @return string
     */
    protected function getValidElementClassName()
    {
        return RemoteWebElement::class;
    }

    /**
     * Saves current page's source into file.
     *
     * @param string $filename
     *
     * @return $this
     */
    protected function savePageSource($filename)
    {
        file_put_contents($filename, $this->getPageSource());

        return $this;
    }

    /**
     * Execute a command on a set of elements.
     *
     * Proxies actions to RemoteWebElement with some initial housekeepings, if needed.
     *
     * @param string|array $action Action(s) to be executed on element.
     * @param string|RemoteWebElement|RemoteWebElement[] $target An element, array of elements or a locator.
     * @param bool $changesUrl Whether the action might change the URL or not.
     *
     * @return $this
     * @throws InvalidArgument
     */
    protected function elementAction($action, $target, $changesUrl = false)
    {
        $elements = $this->isLocator($target)
            ? $this->find($target)
            : $target;

        if (! $this->isElement($elements)) {
            throw new InvalidArgument('No element is targeted to execute the action(s) on.');
        }

        is_array($action) or $action = [$action => []];
        is_array($elements) or $elements = [$elements];

        // Execute all actions for each element
        foreach ($elements as $element) {
            foreach ($action as $method => $args) {
                if (! method_exists($element, $method)) {
                    throw new InvalidArgument("Invalid element action: $method");
                }

                is_array($args) or $args = [$args];

                call_user_func_array([$element, $method], $args);
            }
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
     * 3. ID
     * 4. Value
     * 5. Text
     * 6. XPath
     *
     * NOTE:
     * This is an expensive method; Prefer to utilize explicit find
     * methods instead unless operating in "whadeva" mode!
     *
     * @param string $locator Element locator.
     *
     * @return RemoteWebElement|RemoteWebElement[]
     * @see findBySelector(), findByName(), findById(), findByValue(), findByText(), findByXpath()
     * @todo Guess the type of locator based on its format
     */
    public function find($locator)
    {
        $elements = $this->findBySelector($locator);

        if (empty($elements)) {
            $elements = $this->findByName($locator);

            if (empty($elements)) {
                $elements = $this->findById($locator);

                if (empty($elements)) {
                    $elements = $this->findByValue($locator);

                    if (empty($elements)) {
                        $elements = $this->findByText($locator);

                        if (empty( $elements )) {
                            $elements = $this->findByXpath($locator);
                        }
                    }
                }
            }
        }

        return $elements;
    }

    /**
     * Finds multiple elements by a WebDriverBy instance.
     *
     * @param WebDriverBy $by
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    public function findBy(WebDriverBy $by)
    {
        $elements = $this->driver->findElements($by);

        return empty($elements)
            ? $elements
            // Unwrap the container array if only one element
            : (isset($elements[1]) ? $elements : $elements[0]);
    }

    /**
     * Finds one element by a WebDriverBy instance.
     *
     * @param WebDriverBy $by
     *
     * @return RemoteWebElement
     * @throws NoSuchElement
     */
    public function findOneBy(WebDriverBy $by)
    {
        try {
            return $this->driver->findElement($by);
        } catch (NoSuchElementException $e) {
            throw new NoSuchElement($e);
        }
    }

    /**
     * Find an element by its name attribute.
     *
     * @param string $name
     *
     * @return RemoteWebElement|RemoteWebElement[]
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
     * @return RemoteWebElement|RemoteWebElement[]
     */
    public function findBySelector($selector)
    {
        return $this->findBy(WebDriverBy::cssSelector($selector));
    }

    /**
     * Find an element by its CSS class.
     *
     * @param string $class
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    public function findByClass($class)
    {
        return $this->findBySelector(".$class");
    }

    /**
     * Find an element by its ID attribute.
     *
     * @param string $id
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    public function findById($id)
    {
        return $this->findBy(WebDriverBy::id($id));
    }

    /**
     * Find an element by its value.
     *
     * @param string $value Value to check for.
     * @param string $element Target element tag.
     * @param bool $strict Strict comparison or not.
     *
     * @return RemoteWebElement|RemoteWebElement[]
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
     * @return RemoteWebElement|RemoteWebElement[]
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
     * @return RemoteWebElement|RemoteWebElement[]
     */
    public function findByText($text, $element = '*', $strict = true)
    {
        $op = $strict ? "text()='$text'" : "contains(text(), '$text')";

        return $this->findByXpath("//{$element}[$op]");
    }

    /**
     * Alias for findByText()
     *
     * @param array $args
     *
     * @return RemoteWebElement|\Facebook\WebDriver\Remote\RemoteWebElement[]
     */
    public function findByBody(...$args)
    {
        return $this->findByText(...$args);
    }

    /**
     * Find an element by its containing text.
     *
     * @param string $text Text to check for.
     * @param string $element Target element tag.
     *
     * @return RemoteWebElement|RemoteWebElement[]
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
     * @return RemoteWebElement|RemoteWebElement[]
     */
    public function findByTextOrValue($criteria, $element = '*', $strict = true)
    {
        $elements = $this->findByText($criteria, $element, $strict);

        return empty($elements)
            ? $this->findByValue($criteria, $element, $strict)
            : $elements;
    }

    /**
     * Find an element by its containing text or value.
     *
     * @param string $criteria Text or value to check for.
     * @param string $element Target element tag.
     *
     * @return RemoteWebElement|RemoteWebElement[]
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
     * @return RemoteWebElement|RemoteWebElement[]
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
     * @return RemoteWebElement|RemoteWebElement[]
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
     * @return RemoteWebElement|RemoteWebElement[]
     */
    public function findByXpath($xpath)
    {
        return $this->findBy(WebDriverBy::xpath($xpath));
    }

    /**
     * Find elements by tag name.
     *
     * @param string $tag
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    public function findByTag($tag)
    {
        return $this->findBy(WebDriverBy::tagName($tag));
    }

    /**
     * Types into an element.
     *
     * @param string $text Text to type into the element.
     * @param string|RemoteWebElement $locator Element locator.
     *
     * @return $this
     */
    public function type($text, $locator)
    {
        return $this->elementAction(['sendKeys' => $text], $locator, true);
    }

    /**
     * Hits a single key, hardly.
     *
     * @param string $key Key to hit.
     * @param string|RemoteWebElement $locator Element locator.
     *
     * @return $this
     * @throws InvalidArgument
     * @see WebDriverKeys
     */
    public function hit($key, $locator)
    {
        if ($key[0] !== '\\') {
            $const = WebDriverKeys::class . '::' . strtoupper($key);

            if ( ! defined($const)) {
                throw new InvalidArgument("Invalid key: $key");
            }

            $key = constant($const);
        }

        return $this->type($key, $locator);
    }

    /**
     * Alias for hit().
     *
     * @param array $args
     *
     * @return $this
     */
    public function press(...$args)
    {
        return $this->hit(...$args);
    }

    /**
     * Hits enter.
     *
     * @param string|RemoteWebElement $locator Element locator.
     *
     * @return $this
     */
    public function enter($locator)
    {
        return $this->hit('enter', $locator);
    }

    /**
     * Click on an element.
     *
     * @param string|RemoteWebElement $locator Element locator.
     *
     * @return $this
     */
    public function click($locator)
    {
        return $this->elementAction('click', $locator, true);
    }

    /**
     * Alias for click().
     *
     * @param array $args
     *
     * @return $this
     */
    public function follow(...$args)
    {
        return $this->click(...$args);
    }

    /**
     * Clear an element, if a textarea or an input.
     *
     * @param string|RemoteWebElement $locator Element locator.
     *
     * @return $this
     */
    public function clear($locator)
    {
        return $this->elementAction('clear', $locator);
    }

    /**
     * Fills a field.
     *
     * @param string|RemoteWebElement $locator Element locator.
     * @param string $value
     *
     * @return $this
     */
    public function fillField($locator, $value)
    {
        return $this->type($value, $locator);
    }

    /**
     * Fills a form.
     *
     * @param string|RemoteWebElement $locator Form element locator.
     * @param array $formData Array of name/value pairs as form data for submission.
     *
     * @return $this
     */
    public function fillForm($locator, $formData = [])
    {
        // @TODO: Implement...
    }

    /**
     * Submit a form.
     *
     * @param string|RemoteWebElement $locator Form element locator.
     * @param array $formData Array of name/value pairs as per required by fillForm().
     *
     * @return $this
     */
    public function submitForm($locator, $formData = [])
    {
        // @TODO: Implement...
    }

    /**
     * Submit a form using one of its containing elements.
     *
     * If the element found by locator is a form, or an element within a form, then
     * this will be submitted to the remote server.
     *
     * @param string|RemoteWebElement $locator Element locator.
     *
     * @return $this
     */
    public function submit($locator)
    {
        return $this->elementAction('submit', $locator, true);
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
        $method = $negate ? 'assertNotEquals' : 'assertEquals';

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
        $method = $negate ? 'assertNotRegExp' : 'assertRegExp';

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
        $method = $negate ? 'assertNotEquals' : 'assertEquals';

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
        $method = $negate ? 'assertNotContains' : 'assertContains';

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
     * Checks whether it's a single RemoteWebElement or an array of them.
     *
     * @param mixed $wtf Thing to check.
     *
     * @return bool
     */
    private function isElement($wtf)
    {
        is_array($wtf) and $wtf = end($wtf);

        return $wtf instanceof RemoteWebElement;
    }

    /**
     * Checks whether it's a element locator or nor.
     *
     * @param mixed $wtf Thing to check.
     *
     * @return bool
     */
    private function isLocator($wtf)
    {
        return is_string($wtf);
    }
}
