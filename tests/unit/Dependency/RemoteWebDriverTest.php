<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Dependency;

use Mockery;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Sepehr\PHPUnitSelenium\Tests\Unit\UnitSeleniumTestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class RemoteWebDriverTest extends UnitSeleniumTestCase
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
}
