<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Mockery;
use Facebook\WebDriver\WebDriverBy;
use Sepehr\PHPUnitSelenium\Utils\Filesystem;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Sepehr\PHPUnitSelenium\Exceptions\InvalidArgument;

/**
 * Even though SeleniumTestCase allows setter injections for each
 * of its dependencies regarding better testability (e.g. setWebDriver(),
 * setFilesystem()), it needs to utilize devious hard dependencies to avoid
 * usage complexity and provide ease-of-use for the enduser with a minimum setup.
 *
 * Imagine; you need to write a quick Selenium test and, oh, first you need
 * to inject a bunch of dependencies to the testcase in order to make it work.
 * That sucks, right?
 *
 * To achieve minimum setup requirements, SeleniumTestCase uses hard dependencies
 * by default which are all overridable by setters. Hard dependencies are known to
 * produce hard-to-test code, but on the other hand they bring ease of use for you.
 * So, we take the deep dive here and test the hard-to-test code. You go enjoy the
 * ease of use!
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class DependencyManagementTest extends UnitSeleniumTestCase
{

    /** @test */
    public function doesNotCreateAWebDriverOnSetUp()
    {
        $this->assertFalse($this->webDriverLoaded());
    }

    /**
     * @test
     *
     * We can easily inject a mocked WebDriver into the testcase,
     * but here we're testing the process of instantiating it.
     */
    public function createsAWebDriverInstanceUponCreatingNewSessions()
    {
        $this->webDriverMock
             ->shouldReceive('create')
             ->once()
             ->with(
                 $this->host,
                 DesiredCapabilities::class,
                 $this->connectionTimeout,
                 $this->requestTimeout,
                 $this->httpProxy,
                 $this->httpProxyPort
             )
             ->andReturn(Mockery::self())
             ->mock();

        Mockery::mock('alias:' . DesiredCapabilities::class)
               ->shouldReceive($this->browser)
               ->once()
               ->andReturn(Mockery::self())
               ->mock();

        $this->createSession();

        $this->assertTrue($this->webDriverLoaded());
    }

    /** @test */
    public function doesNotCreateANewWebDriverIfAlreadyExists()
    {
        $this->injectMockedWebDriver(
            $this->webDriverMock
                 ->shouldNotReceive('create')
                 ->getMock()
        );

        $this->createSession();

        $this->assertTrue($this->webDriverLoaded());
    }

    /** @test */
    public function canBeForcedToCreateANewWebDriverEvenThoughItAlreadyExists()
    {
        $this->injectMockedWebDriver(
            $this->webDriverMock
                 ->shouldReceive('create')
                 ->once()
                 ->withAnyArgs()
                 ->andReturn(Mockery::self())
                 ->mock()
        );

        $this->forceCreateSession();

        $this->assertTrue($this->webDriverLoaded());
    }

    /** @test */
    public function unloadsWebDriverWhenDestroyingSession()
    {
        $this->injectMockedWebDriver();

        $this->assertTrue($this->webDriverLoaded());

        // We do not need to set an expectation to receive a "quit()"
        // call on the WebDriver, as it's already set by default.
        // See: UnitSeleniumTestCase::setUp()
        $this->destroySession();

        $this->assertFalse($this->webDriverLoaded());
    }

    /** @test */
    public function returnsItsInstanceOfWebDriver()
    {
        $this->injectMockedWebDriver();

        $this->assertInstanceOf(RemoteWebDriver::class, $this->webDriver());
    }

    /** @test */
    public function createsADesiredCapabilitiesInstanceForAValidBrowserThroughShortcutMethods()
    {
        Mockery::mock('alias:' . DesiredCapabilities::class)
               // e.g. DesiredCapabilities::firefox()
               ->shouldReceive($browser = 'firefox')
               ->once()
               ->withNoArgs()
               ->andReturn(Mockery::self())
               ->mock();

        $this->setBrowser($browser);

        $this->assertInstanceOf(
            DesiredCapabilities::class,
            $this->createDesiredCapabilitiesInstance()
        );
    }

    /** @test */
    public function createsADesiredCapabilitiesInstanceForAValidBrowserThroughInstantiating()
    {
        $this->setBrowser($browser = WebDriverBrowserType::KONQUEROR);

        // e.g. new DesiredCapabilities([...])
        Mockery::mock('overload:' . DesiredCapabilities::class);

        $this->assertInstanceOf(
            DesiredCapabilities::class,
            $this->createDesiredCapabilitiesInstance()
        );
    }

    /** @test */
    public function createsAnInstanceOfWebDriverBy()
    {
        Mockery::mock('alias:' . WebDriverBy::class)
               ->shouldReceive($mechanism = 'id')
               ->once()
               ->with($value = 'someElementId')
               ->andReturn(Mockery::self())
               ->mock();

        $this->assertInstanceOf(
            WebDriverBy::class,
            $this->createWebDriverByInstance($mechanism, $value)
        );
    }

    /** @test */
    public function throwsAnExceptionWhenCreatingAnInstanceOfWebDriverByWithInvalidMechanism()
    {
        Mockery::mock('alias:' . WebDriverBy::class)
               ->shouldReceive($mechanism = 'invalidMechanism')
               ->once()
               ->with($value = 'someValue')
               ->andThrow(\Exception::class);

        $this->expectException(InvalidArgument::class);

        $this->createWebDriverByInstance($mechanism, $value);
    }

    /** @test */
    public function createsAFilesystemInstance()
    {
        Mockery::mock('overload:' . Filesystem::class);

        $this->assertInstanceOf(
            Filesystem::class,
            $this->createFilesystemInstance()
        );
    }
}
