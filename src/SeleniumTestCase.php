<?php

namespace Sepehr\PHPUnitSelenium;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverPlatform;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Exception\WebDriverCurlException;
use Facebook\WebDriver\Exception\NoSuchElementException;

use Sepehr\PHPUnitSelenium\Util\Filesystem;
use Sepehr\PHPUnitSelenium\Exception\NoSuchElement;
use Sepehr\PHPUnitSelenium\Exception\InvalidArgument;
use Sepehr\PHPUnitSelenium\Exception\SeleniumNotRunning;

/**
 * SeleniumTestCase Class
 *
 * NOTE:
 * Even though SeleniumTestCase allows setter injections for each
 * of its dependencies regarding better testability (e.g. setWebDriver(),
 * setFilesystem()), it needs to utilize devious hard dependencies to avoid
 * usage complexity and provide ease-of-use for the enduser with a minimum
 * possible setup.
 *
 * Imagine; you need to write a quick Selenium test and, oh, first you need
 * to inject a bunch of dependencies to the testcase in order to make it work.
 * That sucks, right?
 *
 * To achieve minimum setup requirements, SeleniumTestCase uses hard dependencies
 * by default which are all overridable by setters. Hard dependencies are known to
 * produce hard-to-test code, but on the other hand they bring ease of use for you,
 * the reader! So, I take the deep dive and test the hard-to-test code. You go enjoy
 * the ease of use!
 */
abstract class SeleniumTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * Instance of RemoteWebDriver.
     *
     * @var RemoteWebDriver
     */
    protected $webDriver;

    /**
     * Instance of DesiredCapabilities.
     *
     * @var DesiredCapabilities
     */
    protected $desiredCapabilities;

    /**
     * Holds an instance of filesystem.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Current URL.
     *
     * @var string
     */
    protected $currentUrl;

    /**
     * Browser name.
     *
     * @var string
     */
    protected $browser = WebDriverBrowserType::FIREFOX;

    /**
     * Platform name.
     *
     * @var string
     */
    protected $platform = WebDriverPlatform::ANY;

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
    protected $httpProxy;

    /**
     * Webdriver proxy port.
     *
     * @var int|null
     */
    protected $httpProxyPort;

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
        if ($force || ! $this->webDriverLoaded()) {
            try {
                $this->setupDesiredCapabilities();
                $this->setupWebDriver($force);
            } catch (WebDriverCurlException $e) {
                throw new SeleniumNotRunning($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * Forces to create a new session even though one already exists.
     *
     * @return $this
     */
    protected function forceCreateSession()
    {
        return $this->createSession(true);
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

        $this->webDriver = null;

        return $this;
    }

    /**
     * Sets the internal webdriver instance.
     *
     * @param RemoteWebDriver $webDriver
     *
     * @return $this
     */
    protected function setWebDriver(RemoteWebDriver $webDriver)
    {
        $this->webDriver = $webDriver;

        return $this;
    }

    /**
     * Creates and sets an instance of RemoteWebDriver only if necessary.
     *
     * @param bool $force
     *
     * @return $this
     */
    protected function setupWebDriver($force = false)
    {
        if ($force || ! $this->webDriver instanceof RemoteWebDriver) {
            $this->setWebDriver(
                $this->createWebDriverInstance()
            );
        }

        return $this;
    }

    /**
     * Returns webdriver instance.
     *
     * @return RemoteWebDriver
     */
    protected function webDriver()
    {
        return $this->webDriver;
    }

    /**
     * Check if webdriver is loaded.
     *
     * @return bool
     */
    protected function webDriverLoaded()
    {
        return $this->webDriver instanceof RemoteWebDriver;
    }

    /**
     * RemoteWebDriver factory.
     *
     * @return RemoteWebDriver
     */
    protected function createWebDriverInstance()
    {
        return RemoteWebDriver::create(
            $this->host,
            $this->desiredCapabilities(),
            $this->connectionTimeout,
            $this->requestTimeout,
            $this->httpProxy,
            $this->httpProxyPort
        );
    }

    /**
     * WebDriverBy factory.
     *
     * @param string $mechanism
     * @param string $value
     *
     * @return WebDriverBy
     * @throws InvalidArgument
     */
    protected function createWebDriverByInstance($mechanism, $value = '')
    {
        try {
            return WebDriverBy::$mechanism($value);
        } catch (\Exception $e) {
            throw new InvalidArgument("Invalid WebDriverBy mechanism: $mechanism");
        }
    }

    /**
     * Sets the internal DesiredCapabilities instance.
     *
     * @param DesiredCapabilities $capabilities
     *
     * @return $this
     */
    protected function setDesiredCapabilities(DesiredCapabilities $capabilities)
    {
        $this->desiredCapabilities = $capabilities;

        return $this;
    }

    /**
     * Creates and sets a DesiredCapabilities instance only if necessary.
     *
     * @return $this
     */
    protected function setupDesiredCapabilities()
    {
        if (! $this->desiredCapabilities instanceof DesiredCapabilities) {
            $this->setDesiredCapabilities(
                $this->createDesiredCapabilitiesInstance()
            );
        }

        return $this;
    }

    /**
     * Getter for desiredCapabilities property.
     *
     * @return DesiredCapabilities
     */
    protected function desiredCapabilities()
    {
        return $this->desiredCapabilities;
    }

    /**
     * DesiredCapabilities factory.
     *
     * @return DesiredCapabilities
     */
    protected function createDesiredCapabilitiesInstance()
    {
        $this->validateBrowser($this->browser);

        try {
            return call_user_func([DesiredCapabilities::class, $this->browser]);
        } catch (\Exception $e) {
            $this->validatePlatform($this->platform);

            return new DesiredCapabilities([
                WebDriverCapabilityType::BROWSER_NAME => $this->browser,
                WebDriverCapabilityType::PLATFORM     => $this->platform,
            ]);
        }
    }

    /**
     * Sets internal Filesystem instance.
     *
     * @param Filesystem $fs
     *
     * @return $this
     */
    protected function setFilesystem(Filesystem $fs)
    {
        $this->filesystem = $fs;

        return $this;
    }

    /**
     * Creates and sets a Filesystem instance only if necessary.
     *
     * @return $this
     */
    protected function setupFilesystem()
    {
        if (! $this->filesystem instanceof Filesystem) {
            $this->setFilesystem(
                $this->createFilesystemInstance()
            );
        }

        return $this;
    }

    /**
     * Creates an instance of filesystem.
     *
     * @return Filesystem
     */
    protected function createFilesystemInstance()
    {
        return new Filesystem;
    }

    /**
     * Set browser name.
     *
     * Can be any browser name known to WebDriverBrowserType class.
     *
     * @param string $browser
     *
     * @return $this
     */
    protected function setBrowser($browser)
    {
        $this->validateBrowser($browser);

        $this->browser = $browser;

        return $this;
    }

    /**
     * Validates a browser name.
     *
     * @param string $browser
     *
     * @return bool
     * @throws InvalidArgument
     */
    protected function validateBrowser($browser)
    {
        if (in_array($browser, $this->validBrowsers())) {
            return true;
        }

        throw new InvalidArgument("Invalid browser name: $browser");
    }

    /**
     * Set platform name.
     *
     * Can be any platform name known to WebDriverPlatform class.
     *
     * @param string $platform
     *
     * @return $this
     */
    protected function setPlatform($platform)
    {
        $this->validatePlatform($platform);

        $this->platform = $platform;

        return $this;
    }

    /**
     * Validates a platform name.
     *
     * @param string $platform
     *
     * @return bool
     * @throws InvalidArgument
     */
    protected function validatePlatform($platform)
    {
        if (in_array($platform, $this->validPlatforms())) {
            return true;
        }

        throw new InvalidArgument("Invalid platform name: $platform");
    }

    /**
     * Set base URL for all requests.
     *
     * @param string $url Base URL to be set.
     *
     * @return $this
     * @throws InvalidArgument
     */
    protected function setBaseUrl($url)
    {
        if (! $this->validateUrl($url)) {
            throw new InvalidArgument("Invalid base URL provided: $url");
        }

        $this->baseUrl = $url;

        return $this;
    }

    /**
     * Set current URL.
     *
     * @param string $url URL to be set as current URL.
     *
     * @return $this
     * @throws InvalidArgument
     */
    protected function setUrl($url)
    {
        $url = $this->normalizeUrl($url);

        if (! $this->validateUrl($url)) {
            throw new InvalidArgument("Invalid URL provided: $url");
        }

        $this->currentUrl = $url;

        return $this;
    }

    /**
     * Validates provided URL.
     *
     * @param string $url
     *
     * @return bool
     */
    protected function validateUrl($url)
    {
        return !! filter_var($url, FILTER_VALIDATE_URL);
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
    protected function webDriverUrl()
    {
        return $this->webDriver->getCurrentURL();
    }

    /**
     * Updates URL based on driver's current URL.
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

        $this->setUrl($this->webDriverUrl());

        return $this;
    }

    // ----------------------------------------------------------------------------
    // Page Interaction
    // ----------------------------------------------------------------------------

    /**
     * Visit a URL.
     *
     * @param string $url URL to visit.
     *
     * @return $this
     */
    protected function visit($url = '/')
    {
        $this->createSession();

        $this->setUrl($url);

        $this->webDriver->get($this->url());

        return $this->updateUrl();
    }

    /**
     * Returns current page's title.
     *
     * @return string
     */
    protected function pageTitle()
    {
        return $this->webDriver->getTitle();
    }

    /**
     * Returns current page's source.
     *
     * @return string
     */
    protected function pageSource()
    {
        return $this->webDriver->getPageSource();
    }

    /**
     * Saves current page's source into file.
     *
     * @param string $filepath
     *
     * @return $this
     * @throws InvalidArgument
     */
    protected function savePageSource($filepath)
    {
        $this->setupFilesystem();

        try {
            $this->filesystem->put($filepath, $this->pageSource());
        } catch (\Exception $e) {
            throw new InvalidArgument("Could not write the page source to file: $filepath");
        }

        return $this;
    }

    /**
     * Wait :)
     *
     * @param int $seconds Number of seconds to wait.
     *
     * @return $this
     */
    protected function wait($seconds = 3)
    {
        sleep($seconds);

        return $this;
    }

    // ----------------------------------------------------------------------------
    // Element Query
    // ----------------------------------------------------------------------------

    /**
     * Tries to find elements by examining CSS selector, name, value or text.
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
    protected function find($locator)
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

                        if (empty($elements)) {
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
    protected function findBy(WebDriverBy $by)
    {
        $elements = $this->webDriver->findElements($by);

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
    protected function findOneBy(WebDriverBy $by)
    {
        try {
            return $this->webDriver->findElement($by);
        } catch (NoSuchElementException $e) {
            throw new NoSuchElement($e);
        }
    }

    /**
     * Find elements by its name attribute.
     *
     * @param string $name
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findByName($name)
    {
        return $this->findBy(WebDriverBy::name($name));
    }

    /**
     * Find elements by its CSS selector.
     *
     * @param string $selector
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findBySelector($selector)
    {
        return $this->findBy(WebDriverBy::cssSelector($selector));
    }

    /**
     * Find elements by its CSS class.
     *
     * @param string $class
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findByClass($class)
    {
        return $this->findBySelector(".$class");
    }

    /**
     * Find elements by its ID attribute.
     *
     * @param string $id
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findById($id)
    {
        return $this->findBy(WebDriverBy::id($id));
    }

    /**
     * Find elements by attributes.
     *
     * @param string $attribute Target attribute, e.g. href.
     * @param string|null $value Value to check for.
     * @param string $element Target element tag.
     * @param bool $strict Strict comparison or not.
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findByAttribute($attribute, $value = null, $element = '*', $strict = true)
    {
        $op    = $strict ? '=' : '*=';
        $query = $value ? "{$attribute}{$op}'{$value}'" : $attribute;

        return $this->findBySelector("{$element}[$query]");
    }

    /**
     * Find elements by partial attributes.
     *
     * @param string $attribute Target attribute, e.g. href.
     * @param string $value Value to check for.
     * @param string $element Target element tag.
     * @param bool $strict Strict comparison or not.
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findByPartialAttribute($attribute, $value, $element = '*', $strict = true)
    {
        return $this->findByAttribute($attribute, $value, $element, false);
    }

    /**
     * Find elements by its value.
     *
     * @param string $value Value to check for.
     * @param string $element Target element tag.
     * @param bool $strict Strict comparison or not.
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findByValue($value, $element = '*', $strict = true)
    {
        return $this->findByAttribute('value', $value, $element, $strict);
    }

    /**
     * Find elements by its containing value.
     *
     * @param string $value Value to check for.
     * @param string $element Target element tag.
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findByPartialValue($value, $element = '*')
    {
        return $this->findByValue($value, $element, false);
    }

    /**
     * Find elements by its text.
     *
     * @param string $text Text to check for.
     * @param string $element Target element tag.
     * @param bool $strict Strict comparison or not.
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findByText($text, $element = '*', $strict = true)
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
    protected function findByBody(...$args)
    {
        return $this->findByText(...$args);
    }

    /**
     * Find elements by its containing text.
     *
     * @param string $text Text to check for.
     * @param string $element Target element tag.
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findByPartialText($text, $element = '*')
    {
        return $this->findByText($text, $element, false);
    }

    /**
     * Find elements by its text or value.
     *
     * @param string $criteria Text or value to check for.
     * @param string $element Target element tag.
     * @param bool $strict Strict comparison or not.
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findByTextOrValue($criteria, $element = '*', $strict = true)
    {
        $elements = $this->findByText($criteria, $element, $strict);

        return empty($elements)
            ? $this->findByValue($criteria, $element, $strict)
            : $elements;
    }

    /**
     * Find elements by its containing text or value.
     *
     * @param string $criteria Text or value to check for.
     * @param string $element Target element tag.
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findByPartialTextOrValue($criteria, $element = '*')
    {
        return $this->findByTextOrValue($criteria, $element, false);
    }

    /**
     * Finds elements by ID or name.
     *
     * @param string $criteria Name or ID to check for.
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findByNameOrId($criteria)
    {
        $criteria[0] === '#' and $criteria = substr($criteria, 1);

        $elements = $this->findByName($criteria);

        if (empty($elements)) {
            $elements = $this->findById($criteria);
        }

        return $elements;
    }

    /**
     * Find elements by its link text.
     *
     * @param string $text
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findByLinkText($text)
    {
        return $this->findBy(WebDriverBy::linkText($text));
    }

    /**
     * Find elements by its partial link text.
     *
     * @param string $partialText
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findByLinkPartialText($partialText)
    {
        return $this->findBy(WebDriverBy::partialLinkText($partialText));
    }

    /**
     * Find links by the href attribute.
     *
     * @param string $href
     * @param bool $strict
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findByLinkHref($href, $strict = true)
    {
        return $this->findByAttribute('href', $href, 'a', $strict);
    }

    /**
     * Find links by partial href attribute.
     *
     * @param string $href
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findByLinkPartialHref($href)
    {
        return $this->findByLinkHref($href, false);
    }

    /**
     * Find elements by XPath.
     *
     * @param $xpath
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findByXpath($xpath)
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
    protected function findByTag($tag)
    {
        return $this->findBy(WebDriverBy::tagName($tag));
    }

    /**
     * Find elements by tabindex.
     *
     * @param int $tabIndex
     * @param string $element
     *
     * @return RemoteWebElement|RemoteWebElement[]
     */
    protected function findByTabIndex($tabIndex, $element = '*')
    {
        return $this->findByAttribute('tabindex', (string) $tabIndex, $element, true);
    }

    // ----------------------------------------------------------------------------
    // Element Interaction
    // ----------------------------------------------------------------------------

    /**
     * Types into an element.
     *
     * @param string $text Text to type into the element.
     * @param string|RemoteWebElement $locator Element locator.
     *
     * @return $this
     */
    protected function type($text, $locator)
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
    protected function hit($key, $locator)
    {
        if ($key[0] !== '\\') {
            $const = WebDriverKeys::class . '::' . strtoupper($key);

            if (! defined($const)) {
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
    protected function press(...$args)
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
    protected function enter($locator)
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
    protected function click($locator)
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
    protected function follow(...$args)
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
    protected function clear($locator)
    {
        return $this->elementAction('clear', $locator);
    }

    // ----------------------------------------------------------------------------
    // Form Interaction
    // ----------------------------------------------------------------------------

    /**
     * Fills a field.
     *
     * @param string|RemoteWebElement $locator Element locator.
     * @param string $value
     *
     * @return $this
     */
    protected function fillField($locator, $value)
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
    protected function fillForm($locator, $formData = [])
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
    protected function submitForm($locator, $formData = [])
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
    protected function submit($locator)
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
    protected function assertPageIs($url, $message = '', $negate = false)
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
    protected function seePageIs($url)
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
    protected function dontSeePageIs($url)
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
    protected function assertPageContains($text, $message = '', $negate = false)
    {
        $text = preg_quote($text, '/');
        $method = $negate ? 'assertNotRegExp' : 'assertRegExp';

        $this->$method("/{$text}/i", $this->pageSource(), $message);

        return $this;
    }

    /**
     * Asserts that the page source contains the specified text.
     *
     * @param string $text
     *
     * @return $this
     */
    protected function see($text)
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
    protected function dontSee($text)
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
    protected function assertTitleIs($title, $message = '', $negate = false)
    {
        $method = $negate ? 'assertNotEquals' : 'assertEquals';

        $this->$method($title, $this->pageTitle(), $message);

        return $this;
    }

    /**
     * Assert that the page title matches the given string.
     *
     * @param string $title
     *
     * @return $this
     */
    protected function seeTitle($title)
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
    protected function dontSeeTitle($title)
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
    protected function assertTitleContains($title, $message = '', $negate = false)
    {
        $method = $negate ? 'assertNotContains' : 'assertContains';

        $this->$method($title, $this->pageTitle(), $message);

        return $this;
    }

    /**
     * Assert that the page title contains the given string.
     *
     * @param string $title
     *
     * @return $this
     */
    protected function seeTitleContains($title)
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
    protected function dontSeeTitleContains($title)
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
    protected function assertElementExists($element, $message = '', $negate = false)
    {
        //
    }

    protected function seeElement($element)
    {
        //
    }

    protected function dontSeeElement($element)
    {
        //
    }

    // ----------------------------------------------------------------------------
    // Private Helpers
    // ----------------------------------------------------------------------------

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
    private function elementAction($action, $target, $changesUrl = false)
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

        $path = ltrim($path, '/');

        if (strpos($path, 'http') !== 0) {
            $path = rtrim($this->baseUrl, '/') . '/' . $path;
        }

        return rtrim($path, '/');
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
     * Checks whether it's a element locator or not.
     *
     * @param mixed $wtf Thing to check.
     *
     * @return bool
     */
    private function isLocator($wtf)
    {
        return is_string($wtf);
    }

    /**
     * Returns an array of valid platform names.
     *
     * @return array
     */
    private function validPlatforms()
    {
        return [
            'ANY',
            'ANDROID',
            'LINUX',
            'MAC',
            'UNIX',
            'VISTA',
            'WINDOWS',
            'XP',
        ];
    }

    /**
     * Returns an array of valid browser names.
     *
     * @return array
     */
    private function validBrowsers()
    {
        return [
            'firefox',
            'firefox2',
            'firefox3',
            'firefoxproxy',
            'firefoxchrome',
            'googlechrome',
            'safari',
            'opera',
            'iexplore',
            'iexploreproxy',
            'safariproxy',
            'chrome',
            'konqueror',
            'mock',
            'iehta',
            'android',
            'htmlunit',
            'internet explorer',
            'iphone',
            'iPad',
            'phantomjs',
        ];
    }
}
